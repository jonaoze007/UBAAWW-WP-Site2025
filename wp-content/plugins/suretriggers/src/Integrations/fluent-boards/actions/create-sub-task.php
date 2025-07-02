<?php
/**
 * CreateSubTask.
 * php version 5.6
 *
 * @category CreateSubTask
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FluentBoards\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use FluentBoardsPro\App\Services\SubtaskService;
use FluentBoards\App\Models\Task;

/**
 * CreateSubTask
 *
 * @category CreateSubTask
 * @package  SureTriggers
 */
class CreateSubTask extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'FluentBoards';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'fbs_create_sub_task';

	use SingletonLoader;

	/**
	 * Register an action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Sub Task', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];

		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selected_options.
	 *
	 * @return array|void
	 *
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		
			$task_id     = isset( $selected_options['task_id'] ) ? sanitize_text_field( $selected_options['task_id'] ) : '';
			$title       = isset( $selected_options['title'] ) ? sanitize_text_field( $selected_options['title'] ) : '';
			$description = isset( $selected_options['description'] ) ? sanitize_text_field( $selected_options['description'] ) : '';
			$board_id    = isset( $selected_options['board_id'] ) ? sanitize_text_field( $selected_options['board_id'] ) : '';
			$stage_id    = isset( $selected_options['stage_id'] ) ? sanitize_text_field( $selected_options['stage_id'] ) : '';
			$labels      = isset( $selected_options['labels'] ) ? array_map( 'sanitize_text_field', explode( ',', $selected_options['labels'] ) ) : [];
			$created_by  = isset( $selected_options['created_by'] ) ? sanitize_text_field( $selected_options['created_by'] ) : '';

		if ( empty( $task_id ) || empty( $title ) || empty( $board_id ) ) {
			throw new Exception( 'Required fields are missing: task_id, title, or board_id.' );
		}

			
		if ( ! class_exists( '\FluentBoardsPro\App\Services\SubtaskService' ) ) {
			throw new Exception( 'SubtaskService class is not available.' );
		}

		if ( ! class_exists( '\FluentBoards\App\Models\Task' ) ) {
			throw new Exception( __( 'FluentBoards Task model not found.', 'suretriggers' ) );
		}

			
			$task_service = new SubtaskService();

			
			$task = \FluentBoards\App\Models\Task::where( 'id', $task_id )->where( 'board_id', $board_id )->first();
		if ( ! $task ) {
			throw new Exception( 'Invalid Task ID or Board ID. Please check the values and try again.' );
		}

			
			$task_data = [
				'title'       => $title,
				'description' => $description,
				'board_id'    => $board_id,
				'stage_id'    => $stage_id,
				'status'      => 'open',
				'priority'    => 'low',
				'labels'      => $labels,
				'created_by'  => $created_by,
				'parent_id'   => $task->id,
			];

			$sub_task = $task_service->createSubtask( $task->id, $task_data );

			if ( empty( $sub_task ) ) {
				throw new Exception( 'There was an error creating the Sub Task.' );
			}

			return $sub_task;

	} 
}


CreateSubTask::get_instance();
