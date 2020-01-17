<?php
namespace GuoJiangClub\Catering\AlbumBackend\Repository;

use GuoJiangClub\Catering\AlbumBackend\Models\Image;
use Prettus\Repository\Eloquent\BaseRepository;

class ImageRepository extends BaseRepository
{

    public function model()
    {
        return Image::class;
    }
    public function getImgList()
    {
        
    }
}