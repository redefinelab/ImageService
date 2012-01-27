<?php

namespace RedefineLab\ImageService;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ImageServiceProvider implements ServiceProviderInterface {

    public function register(Application $app) {
        $app['image'] = $app->share(function() {
            return new ImageService;
        });
    }

}