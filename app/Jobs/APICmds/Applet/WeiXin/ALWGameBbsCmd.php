<?php
/**
 * User: Administrator
 * Date: 2016/12/15
 * Time: 15:34
 */

namespace App\Jobs\APICmds\Applet\WeiXin;


use App\JsonParse\JErrorCode;
use App\Models\GameBbs;
use App\Models\GameBbsComment;
use App\Models\GameBbsLike;
use App\Models\WXUser;
use App\Util\TimeUtil;
use App\Util\Tool;

class ALWGameBbsCmd extends BaseCmd
{
    public function __construct($jsonData)
    {
        parent::__construct($jsonData);
        $this->logPath .= 'gamebbs/';
    }

    public function addGameBbs()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->content) || !isset($data->photoList))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $modifyValue = 300; //压缩图最长边px
            $thumbArr = [];
            foreach ($data->photoList as $item) {
                $pathSplitArr = explode('.', $item);

                $tPath = $pathSplitArr[0] . '_thumb.' . $pathSplitArr[1];
                $absoluteTPath = public_path() . $tPath;

                $thumbArr[] = $tPath;

                $img = \Image::make(public_path() . $item);

                $width = $img->width();
                $height = $img->height();

                if ($width > $height && $width > $modifyValue)
                    $img->widen($modifyValue);
                else if ($height > $width && $height > $modifyValue)
                    $img->heighten($modifyValue);

                $img->save($absoluteTPath);
            }

            $modelData = [
                'cl_UserId' => $user->user_id,
                'cl_CreateTime' => TimeUtil::getChinaTime(),
                'cl_Content' => $data->content,
                'cl_Photos' => implode(',', $data->photoList),
                'cl_Thumbs' => implode(',', $thumbArr),
            ];

            GameBbs::create($modelData);

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getGameBbs()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->pageIndex) || !isset($data->pageSize) || !isset($data->orderType))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $dataList = GameBbs::valid()->orderType($data->orderType)->page($data->pageIndex)->paginate($data->pageSize);
            foreach ($dataList as $item) {

                $this->result_list[] = $this->setGameBbsInfo($item, $user->user_id);

            }

            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getGameBbsDetail()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->bbsId))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $bbs = GameBbs::find($data->bbsId);
            $this->result_param = $this->setGameBbsInfo($bbs, $user->user_id);

            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function likeGameBbs()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->bbsId))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $bbs = GameBbs::find($data->bbsId);
            $bbs->increment('cl_Like');

            $modelData = [
                'cl_BbsId' => $bbs->cl_Id,
                'cl_UserId' => $user->user_id,
                'cl_CreateTime' => TimeUtil::getChinaTime()
            ];

            GameBbsLike::create($modelData);

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getGameBbsComment()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->pageIndex) || !isset($data->pageSize) || !isset($data->bbsId))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $dataList = GameBbsComment::with('user')->valid()->where('cl_BbsId', $data->bbsId)->orderby('cl_CreateTime', 'asc')->page($data->pageIndex)->paginate($data->pageSize);
            foreach ($dataList as $item) {
                $this->result_list[] = $this->setGameBbsCommentInfo($item);

            }
            $this->result_param['total'] = $dataList->total();
            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }


    public function commentGameBbs()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->bbsId) || !isset($data->content) || !isset($data->type))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            if ($data->type == 1 && (!isset($data->pid)))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $bbs = GameBbs::find($data->bbsId);
            $bbs->increment('cl_Comment');

            if (isset($data->pid) && $data->type == 1) {
                $parentComment = GameBbsComment::find($data->pid);
            }

            $modelData = [
                'cl_BbsId' => $bbs->cl_Id,
                'cl_UserId' => $user->user_id,
                'cl_PId' => isset($parentComment) ? $parentComment->cl_Id : 0,
                'cl_Content' => $data->content,
                'cl_ToUserId' => isset($parentComment) ? $parentComment->cl_UserId : 0,
                'cl_Type' => $data->type,    //0发表,1回复
                'cl_CreateTime' => TimeUtil::getChinaTime()
            ];

            $retId = GameBbsComment::insertGetId($modelData);
            if (empty($retId))
                return $this->errori('评论失败，请稍候重试');

            $this->result_param['commentSum'] = GameBbsComment::valid()->where('cl_BbsId', $bbs->cl_Id)->count();
            $this->result_param['commentItem'] = $this->setGameBbsCommentInfo(GameBbsComment::find($retId));


            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function deleteGameBbsComment()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->commentId))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;


            $comment = GameBbsComment::find($data->commentId);
            if (empty($comment))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            if ($comment->cl_UserId != $user->user_id)
                return $this->errori('只能删除自己的评论');

            $bbs = GameBbs::find($comment->cl_BbsId);

            $bbs->decrement('cl_Comment');
            $comment->delete();

            $this->result_param['commentSum'] = GameBbsComment::valid()->where('cl_BbsId', $bbs->cl_Id)->count();
            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }


    private function setGameBbsInfo($item, $iUserId, $type = 1)
    {
        $result_param['id'] = $item->cl_Id;

        $result_param['content'] = $item->cl_Content;
        $result_param['userName'] = $item->user->alias;
        $result_param['userHead'] = $item->user->getHeadImg();
        $result_param['time'] = $item->cl_CreateTime;
        $result_param['like'] = $item->cl_Like;
        $result_param['comment'] = $item->cl_Comment;
        $result_param['isHot'] = $item->cl_IsHot;
        $result_param['photos'] = $item->getPhotoArr();
        $result_param['thumbs'] = $item->getThumbArr();
        $result_param['isLike'] = $item->isLike($iUserId);

        if ($type == 1)
            return json_decode(json_encode($result_param));
        return $result_param;
    }

    private function setGameBbsCommentInfo($item, $type = 0)
    {
        $touser = $item->touser;
        $result_param = [
            'id' => $item->cl_Id,
            'content' => $item->cl_Content,
            'bbsId' => $item->cl_BbsId,
            'userId' => $item->cl_UserId,
            'userName' => $item->user->alias,
            'userHead' => $item->user->getHeadImg(),
            'toUserId' => $item->cl_ToUserId,
            'toUserName' => null == $touser ? '' : $touser->alias,
            'type' => $item->cl_Type,
            'time' => $item->cl_CreateTime,
        ];

        if ($type == 1)
            return json_decode(json_encode($result_param));
        return $result_param;
    }
}