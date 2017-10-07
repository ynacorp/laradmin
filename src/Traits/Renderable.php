<?php

namespace Shemi\Laradmin\Traits;

use Illuminate\Support\HtmlString;
use Illuminate\View\View;

trait Renderable
{

    public function render($content)
    {
        if ($content instanceof View) {
            $content = $content->render();
        }

        return new HtmlString($content);
    }

}