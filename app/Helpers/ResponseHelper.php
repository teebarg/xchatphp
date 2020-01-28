<?php


namespace App\Helpers;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Response;



class ResponseHelper
{
    const STATUS_SUCCESS = "success";
    const STATUS_ERROR = "error";

    public static function createSuccessResponse(array $data, string $message = "", array $headers = []): Response
    {
        return self::createResponse(self::STATUS_SUCCESS, $data, $message, 0, 200, $headers);
    }

    public static function createResponse(
        string $status = self::STATUS_SUCCESS,
        array $data = [],
        string $message = "",
        int $code = 0,
        int $httpResponseCode = 200,
        array $headers = []
    ): Response {
        $responseData = [
            'status' => $status,
            'data' => $data,
            'message' => $message
        ];
        if (!empty($code)) {
            $responseData['code'] = $code;
        }

        $header    = [
            'Access-Control-Allow-Origin'      => self::getOrigin(),
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers'     => 'X-Requested-With, Content-Type, Origin, Authorization'
        ];
        return Response::create($responseData, $httpResponseCode, array_merge($header, $headers));
    }

    /**
     * Returns the origin for the request. It just spits back whatever Origin
     * request the request specifies or generates one from the $_SERVER info
     * if no Origin header is specified for the request.
     *
     * @return string the origin of the request
     */
    public static function getOrigin(){
        if(request()->headers->get('Origin')){
            return request()->headers->get('Origin');
        }
        $server = request()->server;
        $origin = $server->get('REQUEST_SCHEME') . "://" . $server->get('HTTP_HOST')
            . ":" . $server->get('REMOTE_PORT');
        return $origin;
    }

    public static function createErrorResponse(
        $message,
        $errorCode,
        array $data = [],
        $httpResponseCode = 404
    ): Response {
        return self::createResponse(self::STATUS_ERROR, $data, $message, $errorCode, $httpResponseCode);
    }

    public static function createPaginableData(Paginator $paginator, string $dataKey)
    {
        return self::createPaginableDataUsingMerge(
            $paginator->total(),
            $paginator->currentPage(),
            $paginator->perPage(),
            count($paginator->items()),
            $dataKey,
            array_map(function ($value) {
                return Helper::convertToArray(is_array($value) ? $value : $value->toArray());
            }, $paginator->items())
        );
    }

    public static function createPaginableDataUsingMerge(
        int $total,
        int $page,
        int $limit,
        $itemCount,
        $dataKey = '',
        $items = []
    ) {
        $data = [
            'page' => $page,
            'limit' => $limit,
            'item_count' => $itemCount,
            'total' => $total,
        ];
        if ($data['limit'] > 0) {
            $data['number_of_pages'] = ceil($data['total'] / $data['limit']);
        }
        if (isset($data['number_of_pages']) && $data['page'] >= $data['number_of_pages']) {
            $data['next_page'] = 0;
        } else {
            $data['next_page'] = $data['page'] + 1;
        }
        if (empty($dataKey)) {
            $data = array_merge($data, Helper::convertToArray($items));
        } else {
            $data[$dataKey] = array_map(function ($value) {
                return Helper::convertToArray($value);
            }, $items);
        }
        return $data;
    }

    public static function additionalInfo(string $message = ResponseMessages::ACTION_SUCCESSFUL, int $code = ResponseCodes::ACTION_SUCCESSFUL){
        return ['message' => $message, 'code' => $code, 'status' => self::STATUS_SUCCESS];
    }

}
