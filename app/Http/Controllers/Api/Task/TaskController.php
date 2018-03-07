<?php

namespace App\Http\Controllers\Api\Task;

use App\Models\Access\Task\Task;
use App\Models\Access\TaskAssignees\TaskAssignees;
use App\Models\Access\Priority\Priority;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Task\TaskRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
/**
 * Class TaskController.
 */
class TaskController extends Controller
{
	
	/* Function which will validate data and pass to repository function to store it in database */
	public function add_task(Request $request){
		$validator = Validator::make($request->all(), [
			'name' => 'required|string|max:191',
		]);

		if ($validator->fails()) {
			$errors = $validator->getMessageBag()->toArray();
			return response()->json(array(
				'status' => FALSE,
				'errors' => $errors
			));
		} else {
			$addTask = TaskRepository::add_task($request->all());
			if( count($addTask)>0 && isset($addTask)){
				$response['status'] = TRUE;
				$response['tasks'] = $addTask;
				$response['message'] = "Tasks added successfully.";
				return response()->json($response, 201);
			} else {
				$response['status'] = FALSE;
				$response['tasks'] = array();
				$response['message'] = "Error occured while adding task. Please try again.";
				return response()->json($response, 201);
			}
		}

	}

	/* Function which will validate data and pass to repository function to store it in database */
	public function update_task(Request $request){
		$validator = Validator::make($request->all(), [
			'name' => 'required|string|max:191',
		]);

		if ($validator->fails()) {
			$errors = $validator->getMessageBag()->toArray();
			return response()->json(array(
				'status' => FALSE,
				'errors' => $errors
			));
		} else {
			$updateTask = TaskRepository::update_task($request->all());
			if(count($updateTask)>0 && !empty($updateTask) && isset($updateTask)){
				$upddateTaskAssignee = TaskRepository::update_task_assignee($request, $updateTask["id"]);
				$response['status'] = TRUE;
				$response['tasks'] = $updateTask;
				$response['message'] = "Tasks updated successfully.";
				return response()->json($response, 201);
			} else {
				$response['status'] = FALSE;
				$response['tasks'] = array();
				$response['message'] = "Error occured while updating task. Please try again.";
				return response()->json($response, 201);
			}
		}

	}

	public function get_priorities(){
		$priority = Priority::all();
		$response['status'] = TRUE;
		$response['priorities'] = $priority->toArray();
		return response()->json($response, 201);
	}

	/* get all tasks of project according to project id */
	public function get_all_project_tasks($project_id){
		$allTasks = Task::where("project_id",$project_id)->with("project")->with("user")->with("statuses")->get();

		/* If any task found then return response with fetched task */
		if(count($allTasks)>0 && isset($allTasks) && !empty($allTasks)){
			$response['status'] = TRUE;
			$response['tasks'] = $allTasks->toArray();
			$response['message'] = "Tasks fetched successfully.";
			return response()->json($response, 201);
		} else {

			/* If no task found, then set status false and return response with message */
			$response['status'] = FALSE;
			$response['message'] = "No task found.";
			return response()->json($response, 201);
		}
	}

	public function get_single_task($taskId){
		$getTaskDetails = TaskRepository::get_single_task($taskId);

		/* If task details found then return response with fetched task */
		if(count($getTaskDetails)>0 && isset($getTaskDetails) && !empty($getTaskDetails)){
			$getTaskAssignees = TaskRepository::get_task_assignees($taskId);
			if(count($getTaskAssignees)>0 && isset($getTaskAssignees) && !empty($getTaskAssignees)){
				$response['status'] = TRUE;
				$response['assignees'] = $getTaskAssignees;
				$response['task'] = $getTaskDetails;
				$response['message'] = "Tasks fetched successfully.";
				return response()->json($response, 201);
			} else {
				$response['status'] = TRUE;
				$response['assignees'] = array();
				$response['task'] = $getTaskDetails;
				$response['message'] = "Tasks fetched successfully.";
				return response()->json($response, 201);
			}
		} else {

			/* If task details not found, then set status false and return response with message */
			$response['status'] = FALSE;
			$response['message'] = "Task details not found.";
			return response()->json($response, 201);
		}	
	}

	//function for soft delete a single Task.
	public function destroy($id) {
		try {
			$getTaskAssignees = TaskAssignees::where("task_id", $id)->get();
			if (count($getTaskAssignees) > 0 && !empty($getTaskAssignees) && isset($getTaskAssignees)) {
				$assigneesId = array();
				foreach ($getTaskAssignees as $taskAssignee) {
					array_push($assigneesId, $taskAssignee->user_id);
				}
				$deleteStatus = TaskRepository::delete_task_assignee($assigneesId, $id);
				$deleteAssigneeStatus = $deleteStatus;
			} else {
				$deleteAssigneeStatus = true;
			}
			if ($deleteAssigneeStatus) {
				$deleteSingletask = Task::find($id);
				if ($deleteSingletask->forceDelete()) {
					$response['status'] = TRUE;
					$response['message'] = "Task delete successfully.";
					return response()->json($response, 200);
				}
			} else {
				$response['status'] = False;
				$response['message'] = "Error occured while deleting tasks assignees.";
				return response()->json($response, 200);
			}
		} catch (\Exception $e) {
			$response['status'] = FALSE;
			$response['errors'] = TRUE;
			$response['message'] = $e->getMessage();
			return response()->json($response, 500);
		}
	}
}

