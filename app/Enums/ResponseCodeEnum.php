<?php

namespace App\Enums;

enum ResponseCodeEnum :int
{
    case OK = 200;
    case CREATED = 201;
    case NO_CONTENT = 204;
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case UNPROCESSABLE_ENTITY = 422;
    case INTERNAL_SERVER_ERROR = 500;
    case SERVICE_UNAVAILABLE = 503;

    public function message(): string
    {
        return match ($this) {
            self::OK => __('global.okay'),
            self::CREATED => __('global.created'),
            self::NO_CONTENT => __('global.no_content'),
            self::BAD_REQUEST => __('global.bad_request'),
            self::UNAUTHORIZED => __('global.unauthorized'),
            self::FORBIDDEN => __('global.forbidden'),
            self::NOT_FOUND => __('global.not_found'),
            self::METHOD_NOT_ALLOWED => __('global.method_not_allowed'),
            self::UNPROCESSABLE_ENTITY => __('global.unprocessable_entity'),
            self::INTERNAL_SERVER_ERROR => __('global.internal_server_error'),
            self::SERVICE_UNAVAILABLE => __('global.service_unavailable'),
        };
    }
}
