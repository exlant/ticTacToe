<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class TestController
 *
 * @package App\Controller
 */
class TestController
{
    /**
     * @return Response
     */
    public function test(): Response
    {
        return new Response('It works!');
    }
}