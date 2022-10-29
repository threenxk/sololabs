<?php
namespace admin\controllers\product;

class ComicAnnouncementController extends ProductBaseController
{
    public $name = '公告';

    public $modelClass = 'admin\models\comic\ComicAnnouncement';
    public $searchModelClass = 'admin\models\comic\ComicAnnouncementSearch';
}
