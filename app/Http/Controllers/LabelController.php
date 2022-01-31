<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Label;
use App\Models\LabelNote;
use App\Exceptions\FundooNoteException;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


/**
 * This controller is responsible for performing CRUD operations 
 */

class LabelController extends Controller
{
    /**
     * This function takes the User access token and labelname
     * creates a label for that respective user.
     */
    /**
     * @OA\Post(
     *   path="/api/auth/createlable",
     *   summary="Create Label",
     *   description=" Create Label ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"labelname"},
     *               @OA\Property(property="labelname", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Label added Sucessfully"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */

    public function createLabel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'labelname' => 'required|string|between:2,15',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $currentUser = JWTAuth::parseToken()->authenticate();
        if ($currentUser) {
            $labelName = Label::where('labelname', $request->labelname)->first();
            if ($labelName) {
                Log::alert('Label Created : ', ['email' => $request->email]);
                return response()->json(['message' => 'Label Name already exists'], 401);
            }

            $label = new Label;
            $label->labelname = $request->get('labelname');
            $value = Cache::remember('labels', 3600, function () {
                return DB::table('labels')->get();
            });

            if ($currentUser->labels()->save($label)) {
                return response()->json(['message' => 'Label added Sucessfully'], 201);
            }
            return response()->json(['message' => 'Could not add label'], 405);
        }
        return response()->json(['message' => 'Invalid authorization token'], 404);
    }
    /**
     * This function takes the User access token and note id and 
     * creates a label for that respective note is and user.
     * 
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *   path="/api/auth/addlabelbynoteid",
     *   summary="Add Label By Note Id",
     *   description=" Add Label By Note Id ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"label_id" , "note_id"},
     *               @OA\Property(property="label_id", type="integer"),
     *               @OA\Property(property="note_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Label Added to Note Sucessfully"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function addLabelByNoteId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'label_id' => 'required',
            'note_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        } 
        
        $currentUser = JWTAuth::parseToken()->authenticate();

        if ($currentUser) {
            $label_id = $request->input('label_id');
            $note_id = $request->input('note_id');

            $label = $currentUser->labels()->find($label_id);

            if (!$label) {
                return response()->json(['message' => 'Label not Found'], 404);
            }

            $note = $currentUser->notes()->find($note_id);

            if (!$note) {
                return response()->json(['message' => 'Notes not Found'], 404);
            }

            $labelNote = new LabelNote();
            $labelNote->note_id = $request->get('note_id');
            $labelNote->label_id = $request->get('label_id');
            $notes = LabelNote::WHERE( 'note_id' ,$request->note_id)->where('label_id',$request->lebel_id);
            if($notes){
                return response()->json(['message' => 'label_id and note_id already exits']);
            }

            if ($currentUser->labelnote()->save($labelNote)) {
                return response()->json(['message' => 'Label Added to Note Sucessfully'], 201);
            }
            return response()->json(['message' => 'Label Did Not added to Note'], 403);
        }
        return response()->json(['message' => 'Invalid authorization token'], 404);
    }

    /**
     * This function takes the User access token and label id and 
     * displays that respective label id.
     * 
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Get(
     *   path="/api/auth/displaylable",
     *   summary="Display Label",
     *   description=" Display Label ",
     *   @OA\RequestBody(
     *         
     *    ),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function displayLabelById(Request $request)
    {
        try {
            $User = JWTAuth::parseToken()->authenticate();
            $labels = Label::where('user_id', $User->id)->get();
            if ($labels == '') {
                return response()->json(['message' => 'Label not found'], 404);
            }
            if (!$labels) {
                throw new FundooNoteException("Invalid Authorization token ", 404);
            }
        } catch (FundooNoteException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
        return response()->json([
            'label' => $labels
        ]);
    }

    /**
     * This function takes the User access token and label id and 
     * updates the label for the respective id.
     * 
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *   path="/api/auth/updatelable",
     *   summary="Update Label",
     *   description=" Update label ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id" , "labelname"},
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="labelname", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Label updated Sucessfully"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function updateLabelById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'labelname' => 'required|string|between:2,20',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $id = $request->input('id');
            $currentUser = JWTAuth::parseToken()->authenticate();
            $label = $currentUser->labels()->find($id);

            if (!$label) {
                Log::error('Label Not Found', ['label_id' => $request->id]);
                return response()->json(['message' => 'Label not Found'], 404);
            }

            $label->fill($request->all());
            $value = Cache::remember('labels', 3600, function () {
                return DB::table('labels')->get();
            });

            if ($label->save()) {
                Log::info('Label updated', ['user_id' => $currentUser, 'label_id' => $request->id]);
                return response()->json(['message' => 'Label updated Sucessfully'], 201);
            }
            if (!($label->save())) {
                throw new FundooNoteException("Invalid Authorization token ", 404);
            }
        } catch (FundooNoteException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
        return $label;
    }


    /**
     * This function takes the User access token and label id and 
     * and deleted that particular label id.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *   path="/api/auth/deletelable",
     *   summary="Delete Label",
     *   description=" Delete label ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Label deleted Sucessfully"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function deleteLabelById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $id = $request->input('id');
            $currentUser = JWTAuth::parseToken()->authenticate();
            $label = $currentUser->labels()->find($id);

            if (!$label) {
                Log::error('Label Not Found', ['label_id' => $request->id]);
                return response()->json(['message' => 'Label not Found'], 404);
            }
            $value = Cache::remember('labels', 3600, function () {
                return DB::table('labels')->get();
            });

            if ($label->delete()) {
                Log::info('Label deleted', ['user_id' => $currentUser, 'label_id' => $request->id]);
                return response()->json(['message' => 'Label deleted Sucessfully'], 201);
            }
            if (!($label->delete())) {
                throw new FundooNoteException("Invalid Authorization token ", 404);
            }
        } catch (FundooNoteException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }
}
