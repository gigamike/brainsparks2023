<?php

defined('BASEPATH') OR exit('No direct script access allowed');


if (!function_exists('media_icon_helper')) {

    function media_icon_helper($media) {
      if($media->type == 'directory'){
        return '<div class="icon"><i class="img-responsive fa fa-folder"></i></div>';
      }elseif($media->type == 'file'){
        if($media->mime_type == 'image/jpg'
                || $media->mime_type == 'image/jpeg'
                || $media->mime_type == 'image/png'
                || $media->mime_type == 'image/gif'){
          return '<div class="icon"><i class="img-responsive fa fa-picture-o"></i></div>';
        }elseif($media->mime_type == 'application/pdf'){
          return '<div class="icon"><i class="img-responsive fa fa-file-pdf-o"></i></div>';
        }elseif($media->mime_type == 'video/mp4'
                || $media->mime_type == 'application/mp4'){
          return '<div class="icon"><i class="img-responsive fa fa-film"></i></div>';
        }elseif($media->mime_type == 'text/plain'){
          return '<div class="icon"><i class="img-responsive fa fa-file-text"></i></div>';
        }elseif($media->mime_type == 'text/csv'
                || $media->mime_type == 'application/vnd.ms-excel'
                || $media->mime_type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
          return '<div class="icon"><i class="img-responsive fa fa-file-excel-o"></i></div>';
        }elseif($media->mime_type == 'application/msword'
                || $media->mime_type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'){
          return '<div class="icon"><i class="img-responsive fa fa-file-word-o"></i></div>';
        }elseif($media->mime_type == 'application/vnd.ms-powerpoint'
                || $media->mime_type == 'application/vnd.openxmlformats-officedocument.presentationml.presentation'){
          return '<div class="icon"><i class="img-responsive fa fa-file-powerpoint-o"></i></div>';
        }else{
          return '<div class="icon"><i class="img-responsive fa fa-film"></i></div>';
        }
      }
    }
}
