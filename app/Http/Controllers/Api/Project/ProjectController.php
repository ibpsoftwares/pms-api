<?php

namespace App\Http\Controllers\Api\Project;

use App\Models\Access\Project\Project;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Project\ProjectRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
/**
 * Class ProjectController.
 */
class ProjectController extends Controller
{ 

    /* Function called from route file to create new project */
    public function create_project(Request $request){

        /* Validator is used to validate all the details which are recived in the $request */
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'handle' => 'required|string|max:191',
        ]);
        
        /* fails() will return tru only if any of details which validator checks is no valid */
        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
            return response()->json(array(
                'status' => FALSE,
                'errors' => $errors
            ));
        } else {

            /* Check if any entry of handle exist in database */
            $checkProjectHandle = Project::where("handle",$request["handle"])->withTrashed()->get();
            
            if(count($checkProjectHandle) == 0){
                $projectCreate = ProjectRepository::create_project($request->all());
                if(isset($projectCreate) && !empty($projectCreate)){
                    $response['status'] = TRUE;
                    $response['project'] = $projectCreate->toArray();
                    $response['message'] = "Project create successfully.";
                    return response()->json($response, 201);
                } else {
                    $response['status'] = FALSE;
                    $response['message'] = "Some error occured while creating project, please try again.";
                    return response()->json($response, 201);
                }
            } else {
                $response['status'] = FALSE;
                $response['message'] = "Handle already taken.";
                return response()->json($response, 201);
            }
        }
    }

    /* Function will update project details after validating requests */
    public function update_project(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'handle' => 'required|string|max:191',
        ]);
        
        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
            return response()->json(array(
                'status' => FALSE,
                'errors' => $errors
            ));
        } else {

            $checkProjectHandle = Project::where("handle",$request["handle"])->withTrashed()->get();
            if(count($checkProjectHandle) == 0){
                $projectUpdate = ProjectRepository::update_project($request->all());
                if(isset($projectUpdate) && !empty($projectUpdate)){
                    $response['status'] = TRUE;
                    $response['project'] = $projectUpdate->toArray();
                    $response['message'] = "Project updated successfully.";
                    return response()->json($response, 201);
                } else {
                    $response['status'] = FALSE;
                    $response['message'] = "Some error occured while updating, please try again.";
                    return response()->json($response, 201);
                }
            } else if($request["id"] == $checkProjectHandle[0]->id) {
                $projectUpdate = ProjectRepository::update_project($request->all());
                if(isset($projectUpdate) && !empty($projectUpdate)){
                    $response['status'] = TRUE;
                    $response['project'] = $projectUpdate->toArray();
                    $response['message'] = "Project updated successfully.";
                    return response()->json($response, 201);
                } else {
                    $response['status'] = FALSE;
                    $response['message'] = "Some error occured while updating, please try again.";
                    return response()->json($response, 201);
                }
            } else {
                $response['status'] = FALSE;
                $response['message'] = "Handle already taken.";
                return response()->json($response, 201);
            }
        }
    }

    /* Function will generate handle of the project relating with project passed as parameter */
    public function get_project_handle($project_name){

        $projectHandleCreate = ProjectRepository::create_project_handle($project_name);
        $response['status'] = TRUE;
        $response['handle'] = $projectHandleCreate;
        $response['message'] = "Handle generated successfully.";
        return response()->json($response, 201);
    }

    /* Function will return all user which are assigned to the project */
    public function get_all_user_projects(){

        $projects = ProjectRepository::get_all_user_projects();
        if(count($projects)>0 && isset($projects) && !empty($projects)){
            $response['status'] = TRUE;
            $response['projects'] = $projects;
            $response['message'] = "All projects fetched successfully.";
            return response()->json($response, 201);
        } else {
            $response['status'] = FALSE;
            $response['message'] = "No projects found.";
            return response()->json($response, 201);
        }
    }

    /* Function will return single project details depending upon $handle passed as parameter */
    public function get_single_project($handle){

        $project = ProjectRepository::get_single_project($handle);
        if(isset($project) && !empty($project)){
            $response['status'] = TRUE;
            $response['project'] = $project->toArray();
            $response['message'] = "Project fetched successfully.";
            return response()->json($response, 201);
        } else {
            $response['status'] = FALSE;
            $response['message'] = "No projects found.";
            return response()->json($response, 201);
        }

    }

    /* Funciton which will delete project and all of its assignees from the databse */
    public function delete_project($handle){

        $deleteStatus = ProjectRepository::delete_project($handle);
        $response['status'] = TRUE;
        $response['message'] = "Project deleted.";
        return response()->json($response, 201);
    }

}

