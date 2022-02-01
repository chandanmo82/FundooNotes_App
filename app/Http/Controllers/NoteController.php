<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Note;
use App\Exceptions\FundooNoteException;
use Exception;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;


/**
 * @since 13-jan-2022
 * 
 * This controller is responsible for performing CRUD operations 
 * on notes.
 */
class NoteController extends Controller
{

    /**
     * This function takes User access token and checks if it is
     * authorised or not if so and it procees for the note creation 
     * and created it successfully.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *   path="/api/auth/createnote",
     *   summary="Create Note",
     *   description=" Create Note ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"title", "description"},
     *               @OA\Property(property="title", type="string"),
     *               @OA\Property(property="description", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="notes created successfully"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function createNote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|between:2,50',
            'description' => 'required|string|between:3,1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            $note = new Note;
            $note->title = $request->input('title');
            $note->description = $request->input('description');
            $note->user_id = Auth::user()->id;
            $note->save();
            if (!$note) {
                throw new FundooNoteException("Invalid Authorization token ", 404);
            }
            $value = Cache::remember('notes', 3600, function () {
                return DB::table('notes')->get();
            });
        } catch (FundooNoteException $e) {
            Log::error('Invalid User');
            return response()->json([
                'status' => $e->statusCode(),
                'message' => $e->message()
            ]);
        }

        Log::info('notes created', ['user_id' => $note->user_id]);
        return response()->json([
            'status' => 201,
            'message' => 'notes created successfully'
        ]);
    }

    /**
     * This function takes JWT access token and note id and finds 
     * if there is any note existing on that User id and note id if so
     * it successfully returns that note id
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Get(
     *   path="/api/auth/displaynote",
     *   summary="Display Note",
     *   description=" Display Note ",
     *   @OA\RequestBody(
     *         
     *    ),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */

    public function displayNoteById()
    {

        try {
            $currentUser = JWTAuth::parseToken()->authenticate();

            $value = Cache::remember('notes', 3600, function () {
                return DB::table('notes')->get();
            });


            if ($currentUser) {
                $user = Note::leftJoin('labelnote', 'labelnote.note_id', '=', 'notes.id')->leftJoin('labels', 'labels.id', '=', 'labelnote.label_id')
                    ->select('notes.id', 'notes.title', 'notes.description','notes.pin','notes.archive','notes.colour', 'labels.labelname')
                    ->where('notes.user_id', '=', $currentUser->id)->get();

                if ($user == '[]') {
                    return response()->json(['message' => 'Notes not found'], 404);
                }
                return response()->json([
                    'message' => 'All  Notes are Fetched Successfully',
                    'notes' => $user

                ], 201);
            }
            throw new FundooNoteException("Invalid Authorization token ", 404);
        } catch (FundooNoteException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }

    /**
     * This function takes the USer access token and note id which 
     * user wants to update and finds the note id if it is existed
     * or not if so, updates it successfully.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *   path="/api/auth/updatenote",
     *   summary="Update Note",
     *   description=" Update Note ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id" , "title", "description"},
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="title", type="string"),
     *               @OA\Property(property="description", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Note updated Sucessfully"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function updateNoteById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'title' => 'string|between:2,30',
            'description' => 'string|between:3,1000',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $id = $request->input('id');
            $currentUser = JWTAuth::parseToken()->authenticate();
            $note = $currentUser->notes()->find($id);
            $value = Cache::remember('notes', 3600, function () {
                return DB::table('notes')->get();
            });

            if (!$note) {
                Log::error('Notes Not Found', ['id' => $request->id]);
                return response()->json(['message' => 'Notes not Found'], 404);
            }

            $note->fill($request->all());

            if ($note->save()) {
                Log::info('notes updated', ['user_id' => $currentUser, 'note_id' => $request->id]);
                return response()->json(['message' => 'Note updated Sucessfully'], 201);
            }
            if (!($note->save())) {
                throw new FundooNoteException("Invalid Authorization token ", 404);
            }
        } catch (FundooNoteException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }

    /**
     * This function takes the USer access token and note id which 
     * user wants to delete and finds the note id if it is existed
     * or not if so, deletes it successfully.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *   path="/api/auth/deletenote",
     *   summary="Delete Note",
     *   description=" Delete Note ",
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
     *   @OA\Response(response=201, description="Note deleted Sucessfully"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function deleteNoteById(Request $request)
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
            $note = $currentUser->notes()->find($id);
            $value = Cache::remember('notes', 3600, function () {
                return DB::table('notes')->get();
            });

            if (!$note) {
                Log::error('Notes Not Found', ['id' => $request->id]);
                return response()->json(['message' => 'Notes not Found'], 404);
            }

            if ($note->delete()) {
                Log::info('notes deleted', ['user_id' => $currentUser, 'note_id' => $request->id]);
                return response()->json(['message' => 'Note deleted Sucessfully'], 201);
            }
            if (!($note->delete())) {
                throw new FundooNoteException("Invalid Authorization token ", 404);
            }
        } catch (FundooNoteException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }
    /**
     * Functio used to view all notes
     * ie; 3 notes per page wise  will be displayed.
     */
    /**
     * @OA\Get(
     *   path="/api/auth/paginatenote",
     *   summary="Display Paginate Notes",
     *   description=" Display Paginate Notes ",
     *   @OA\RequestBody(
     *         
     *    ),
     *   @OA\Response(response=201, description="Pagination aplied to all Notes"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function paginationNote()
    {
        $allNotes = Note::paginate(3);

        return response()->json([
            'message' => 'Pagination aplied to all Notes',
            'notes' =>  $allNotes,
        ], 201);
    }
    /**
     * This function takes the User access token and checks if it 
     * authorised or not and it takes the note_id and pins  it 
     * successfully if notes is exist.  
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *   path="/api/auth/pinnote",
     *   summary="Pin Note",
     *   description=" Pin Note ",
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
     *   @OA\Response(response=201, description="Note Pinned Sucessfully"),
     *   @OA\Response(response=404, description="Notes not Found"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function pinNoteById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $id = $request->input('id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        $note = $currentUser->notes()->find($id);

        if (!$note) {
            Log::error('Notes Not Found', ['user' => $currentUser, 'id' => $request->id]);
            return response()->json(['message' => 'Notes not Found'], 404);
        }

        if ($note->pin == 0) {
            $user = Note::where('id', $request->id)
                ->update(['pin' => 1]);

            Log::info('notes Pinned', ['user_id' => $currentUser, 'note_id' => $request->id]);
            return response()->json(['message' => 'Note Pinned Sucessfully'], 201);
        }

        /*$user = Note::where('id', $request->id)
                         ->update(['pin' => 0]);

        Log::info('notes UnPinned',['user_id'=>$currentUser,'note_id'=>$request->id]);
        return response()->json(['message' => 'Note UnPinned ' ], 201);
        */
    }
    /**
     * This function takes the User access token and checks if it 
     * authorised or not and it takes the note_id and Archives it 
     * successfully if notes exist.  
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *   path="/api/auth/archivenote",
     *   summary="Archive Note",
     *   description=" Archive Note ",
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
     *   @OA\Response(response=201, description="Note Archived Sucessfully"),
     *   @OA\Response(response=404, description="Notes not Found"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function archiveNoteById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $id = $request->input('id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        $note = $currentUser->notes()->find($id);

        if (!$note) {
            Log::error('Notes Not Found', ['user' => $currentUser, 'id' => $request->id]);
            return response()->json(['message' => 'Notes not Found'], 404);
        }

        if ($note->Archive == 0) {
            $user = Note::where('id', $request->id)
                ->update(['archive' => 1]);

            Log::info('notes Archived', ['user_id' => $currentUser, 'note_id' => $request->id]);
            return response()->json(['message' => 'Note Archived Sucessfully'], 201);
        }

        /*$user = Note::where('id', $request->id)
                         ->update(['archive' => 0]);

        Log::info('notes UnArchived',['user_id'=>$currentUser,'note_id'=>$request->id]);
        return response()->json(['message' => 'Note UnArchived ' ], 201);
        */
    }

    /**
     * This function takes the User access token and checks if it 
     * authorised or not and it takes the note_id and colours it 
     * successfully if notes exist.  
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *   path="/api/auth/colournote",
     *   summary="Colour Note",
     *   description=" Colour Note ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id" , "colour"},
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="colour", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Note coloured Sucessfully"),
     *   @OA\Response(response=404, description="Notes not Found"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function colourNoteById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'colour' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $id = $request->input('id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        $note = $currentUser->notes()->find($id);

        if (!$note) {
            Log::error('Notes Not Found', ['user' => $currentUser, 'id' => $request->id]);
            return response()->json(['message' => 'Notes not Found'], 404);
        }

        $colours  =  array(
            'green' => 'rgb(0,255,0)',
            'red' => 'rgb(255,0,0)',
            'blue' => 'rgb(0,0,255)',
            'yellow' => 'rgb(255,255,0)',
            'grey' => 'rgb(128,128,128)',
            'purple' => 'rgb(128,0,128)',
            'brown' => 'rgb(165,42,42)',
            'orange' => 'rgb(255,165,0)',
            'pink' => 'rgb(255,192,203)',
            'black' => 'rgb(0,0,0)',
            'silver' => 'rgb(192,192,192)',
            'teal' => 'rgb(0,128,128)'
        );

        $colour_name = strtolower($request->colour);

        if (isset($colours[$colour_name])) {
            $user = Note::where('id', $request->id)
                ->update(['colour' => $colours[$colour_name]]);

            Log::info('notes coloured', ['user_id' => $currentUser, 'note_id' => $request->id]);
            return response()->json(['message' => 'Note coloured Sucessfully'], 201);
        } else {
            return response()->json(['message' => 'Colour Not Specified in the List'], 400);
        }
    }
}
