<?php
namespace admin\controllers\product;

class AudioAnnouncementController extends ProductBaseController
{
    public $name = '公告';
    public $modelClass = 'admin\models\audio\AudioAnnouncement';
    public $searchModelClass = 'admin\models\audio\AudioAnnouncementSearch';
}

