<?php

declare(strict_types=1);

namespace Jurager\Exchange\Controller;

use Jurager\Exchange1C\Exceptions\Exchange1CException;
use Jurager\Exchange1C\Services\CatalogService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class ImportController.
 */
class ImportController extends Controller
{
    /**
     * @param Request        $request
     * @param CatalogService $service
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function request(Request $request, CatalogService $service)
    {
        $mode = $request->get('mode');
        $type = $request->get('type');

        try {
            if ($type == 'catalog') {
                if (!method_exists($service, $mode)) {
                    throw new Exchange1CException('not correct request, class ExchangeCML not found');
                }

                $response = $service->$mode();
                \Log::debug('exchange_1c: $response='."\n".$response);

                return response($response, 200, ['Content-Type', 'text/plain']);
            } else {
                throw new \LogicException(sprintf('Logic for method %s not released', $type));
            }
        } catch (Exchange1CException $e) {
            \Log::error("exchange_1c: failure \n".$e->getMessage()."\n".$e->getFile()."\n".$e->getLine()."\n");
            
            $message = iconv('utf-8', 'windows-1251', $e->getMessage());

            $response = "failure\n";
            $response .= $message."\n";
            $response .= $e->getFile()."\n";
            $response .= $e->getLine()."\n";

            return response($response, 500, ['Content-Type', 'text/plain']);
        }
    }
}
