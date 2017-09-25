<?php

namespace FSth\Koa\HttpServer;

use FSth\Co\Call;
use FSth\Co\Tool;
use FSth\Koa\Server\Application;
use FSth\Koa\Server\Context;
use FSth\Koa\Server\Request;
use FSth\Koa\Server\Response;

class HttpProtocol
{
    protected $app;
    protected $ctx;

    protected $middleWare;

    public function __construct(Application $app, Context $ctx)
    {
        $this->app;
        $this->ctx;
    }

    public function setMiddleWare($middleWare)
    {
        $this->middleWare = $middleWare;
    }

    public function onRequest(\swoole_http_request $req, \swoole_http_response $res)
    {
        $ctx = $this->createContext($req, $res);
        $reqHandler = $this->makeRequestHandler($ctx);
        $resHandler = $this->makeResponseHandler($ctx);
        Call::spawn($reqHandler, $resHandler);
    }

    public function onWorkerStart(\swoole_http_server $server, $workerId)
    {
        
    }

    protected function makeRequestHandler(Context $ctx)
    {
        return function () use ($ctx) {
            yield Tool::setCtx("ctx", $ctx);
            $ctx->res->status(404);
            $fn = $this->middleWare;
            yield $fn($ctx);
        };
    }

    protected function makeResponseHandler(Context $ctx)
    {
        return function ($r = null, \Exception $ex = null) use ($ctx) {
            if ($ex) {
                $this->handleError($ctx, $ex);
            } else {
                $this->respond($ctx);
            }
        };
    }

    protected function handleError(Context $ctx, \Exception $ex = null)
    {
        if ($ex == null) {
            return;
        }

        $msg = $ex->getMessage();
        if ($ex instanceof \HttpException) {
            $status = $ex->getCode() ?: 500;
            $ctx->res->status($status);
        } else {
            $ctx->res->status(500);
        }
        $ctx->res->header("Content-Type", "text");
        $ctx->res->write($msg);

        $ctx->res->end();
    }

    protected function respond(Context $ctx)
    {
        if ($ctx->respond === false) return;

        $body = $ctx->body;
        $code = $ctx->status;

        if ($code !== null) {
            $ctx->res->status($code);
        }

        if ($body !== null) {
            $ctx->res->write($body);
        }

        $ctx->res->end();
    }

    protected function createContext(\swoole_http_request $req, \swoole_http_response $res)
    {
        $context = clone $this->ctx;

        $request = $context->request = new Request($this->app, $context, $req, $res);
        $response = $context->response = new Response($this->app, $context, $req, $res);

        $context->app = $this;
        $context->req = $req;
        $context->res = $res;

        $request->response = $response;
        $response->request = $request;

        $request->originalUrl = $req->server['request_uri'];
        $request->ip = $req->server['remote_addr'];

        return $context;
    }
}