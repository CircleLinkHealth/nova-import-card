<?php

/**
 * @SWG\Swagger(
 *     basePath="/api/v2.1",
 *     schemes={"http"},
 *     host="clapi.cpm.com",
 *     produces={"application/json"},
 *     consumes={"application/json"},
 *     @SWG\Info(
 *         version="2.1.1",
 *         title="CLH Api",
 *         description="This is CircleLink Health's Api",
 *     ))
* @SWG\Post(
 *     path="/observation",
 *     tags={"observation"},
 *     operationId="createObservation",
 *     summary="Appends comment to daily state_app and creates a new observation",
 *     description="",
 *     consumes={"application/json", "application/xml"},
 *     produces={"application/xml", "application/json"},
 * @SWG\Parameter(
 *         name="Authorization",
 *         type="string",
 *         in="header",
 *         description="Token",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 * @SWG\Parameter(
 *         name="X-Authorization",
 *         in="header",
 *         type="string",
 *         description="API Key",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 * @SWG\Parameter(
 *         name="client",
 *         in="header",
 *         type="string",
 *         description="mobi/ui",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 *     @SWG\Parameter(
 *         name="parent_id",
 *         in="body",
 *         description="Id of the state_app record for the given day",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 *     @SWG\Parameter(
 *         name="obs_message_id",
 *         in="body",
 *         description="Observation Message ID",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 *     @SWG\Parameter(
 *         name="obs_key",
 *         in="body",
 *         description="Observation Key",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 *      @SWG\Parameter(
 *         name="obs_value",
 *         in="body",
 *         description="Observation Value",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 *     @SWG\Parameter(
 *         name="obs_date",
 *         in="body",
 *         description="Created timestamp",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 *     @SWG\Parameter(
 *         name="timezone",
 *         in="body",
 *         description="User's timezone",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 *     @SWG\Response(
 *         response=201,
 *         description="Success",
 *     ),
 *     @SWG\Response(
 *         response=500,
 *         description="Error",
 *     ),
 *     security={{"petstore_auth":{"write:pets", "read:pets"}}}
 * )
 */
