<?php
namespace admin\controllers\product;

class BookAnnouncementController extends ProductBaseController
{
    public $name = '公告';

    public $modelClass = 'admin\models\book\BookAnnouncement';
    public $searchModelClass = 'admin\models\book\BookAnnouncementSearch';
}
