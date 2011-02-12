<?php
// Phalanx
// Copyright (c) 2010 Blue Static
//
// This program is free software: you can redistribute it and/or modify it
// under the terms of the GNU General Public License as published by the Free
// Software Foundation, either version 3 of the License, or any later version.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
// FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
// more details.
//
// You should have received a copy of the GNU General Public License along with
// this program.  If not, see <http://www.gnu.org/licenses/>.

namespace phalanx\data;

require_once PHALANX_ROOT . '/tasks/task.php';

// This interface is meant to be implemented by phalanx\data\Model classes
// if they support automatic validation.
interface ValidatingModel
{
    // Returns an instance of the ModelValidator for this class of Model.
    public function GetValidator();

    // Returns the name that the Model is accessible by.
    static public function ValidatorName();
}

// This abstract class is the base class for validating Model objects. It is
// initialized with a populated instance of ValidatingModel. Models can be
// validated multiple times. For Models that validate, there should be a 1:1
// mapping of Validator subclasses to Model classes.
//
// A Validator works by dynamically dispatching methods based on the field names
// in the Model (from the Struct implementation). Each field should have a
// method that performs validation of that field, and the method should be named
// _validate_field(), with |field| being the name of the field. If a method for
// a field is not defined, the behavior is controlled by the
// |error_on_unvalidated_key| property. This property should be set in
// _OnInitialize() if the default value is not appropriate for your needs.
abstract class ModelValidator
{
    // An instance of the Model that will validate.
    protected $model = NULL;

    // A map, keyed by the Model's fields, of arrays of errors.
    protected $errors = array();

    // Boolean status of validation.
    protected $is_valid = TRUE;

    // If TRUE, the Validator will report an error for any field of the Model
    // that does not have a validation function.
    protected $error_on_unvalidated_key = TRUE;

    // The field that is being validated right now. NULL if not validating.
    private $current_field = NULL;

    // Creates an instance of the Validator for a Model object.
    public function __construct(ValidatingModel $model)
    {
        $this->model = $model;
        $this->_OnInitialize();
    }

    // Called after the base constructor has done it's work and set common
    // properties.
    protected function _OnInitialize() {}

    // Performs validation on the Model and dynamically dispatches methods.
    public function Validate()
    {
        foreach ($this->model->GetFields() as $name) {
            $validator = "_validate_$name";
            $value = $this->model->Get($name);
            if (method_exists($this, $validator)) {
                $this->current_field = $name;
                $this->is_valid      = $this->$validator($value) && $this->is_valid;
                $this->current_field = NULL;
            } else if ($this->error_on_unvalidated_key) {
                $class = $this->model->ValidatorName();
                throw new ModelValidatorException("The field '$name' of '$class' not have a validator.");
            } else {
                $this->is_valid = $this->_DefaultValidate($value) && $this->is_valid;
            }
        }
    }

    // The default validator for fields that do not have defined functions. If
    // |$this->error_on_unvalidated_key| is TRUE, this will never be called.
    protected function _DefaultValidate($value)
    {
        return TRUE;
    }

    // Use this method to report an error in validation from the _validate_()
    // methods.
    protected function _ValidationError($message)
    {
        if ($this->current_field === NULL)
            throw new ModelValidatorException('Calling _ValidationError() in the wrong context');
        $this->errors[$this->current_field][] = $message;
    }

    // This method is used to perform access checks for the various actions of
    // the ValidatingModelTask. Returns TRUE if the task should continue
    // execution and FALSE if it should stop.
    public function FilterAction(ValidatingModelTask $task)
    {
        return TRUE;
    }

    // Getters and setters.
    // -------------------------------------------------------------------------
    public function errors() { return $this->errors; }
    public function is_valid() { return $this->is_valid; }
}

// Exception to be used by the abstract ModelValidator, not by subclasses.
class ModelValidatorException extends \Exception
{}

class ValidatingModelTask extends \phalanx\tasks\Task
{
    static public function InputList()
    {
        return array(
            // The name of Model. This name corresponds to the value from
            // ValidatingModel::ValidatorName().
            'model',
            // Action to perform on the model. See constants below.
            'action',
            // Data with which to populate an instance of |model|.
            'data'
        );
    }

    static public function OutputList()
    {
        return array(
            // The error code. 0 for no error.
            'code',
            // This |errors| field of a ModelValidator instance. Can be NULL.
            'errors',
            // On success, the contents of the Model object. Can be NULL.
            'record'
        );
    }

    // Output properties {{
      protected $code = 0;
      public function code() { return $this->code; }

      protected $errors = NULL;
      public function errors() { return $this->errors; }

      protected $record = NULL;
      public function record() { return $this->record; }
    // }}

    // Internal properties {{
      // The instance of the Model that will be validated.
      protected $model = NULL;
      // The model() property is exposed for FilterAction(). Do not mutate the
      // object (const for the win?).
      public function model() { return $this->model; }

      // A ModelValidator instance for the |$model|.
      protected $validator = NULL;
    // }}

    // Actions {{
      const ACTION_FETCH    = 'fetch';
      const ACTION_DELETE   = 'delete';
      const ACTION_VALIDATE = 'validate';
      const ACTION_INSERT   = 'insert';
      const ACTION_UPDATE   = 'update';
    // }}

    public function Run()
    {
        // Make sure the client is only performing an allowed action.
        $actions = array(self::ACTION_FETCH, self::ACTION_DELETE,
                         self::ACTION_VALIDATE, self::ACTION_INSERT,
                         self::ACTION_UPDATE);
        if (!in_array($this->input->action, $actions)) {
            $this->_Error(-1, 'Action not supported');
            return $this->Cancel();
        }

        // Make sure the Model exists and can be validated.
        $classes = get_declared_classes();
        foreach ($classes as $class) {
            $mirror = new \ReflectionClass($class);
            if ($mirror->ImplementsInterface('\phalanx\data\ValidatingModel')) {
                $validator_name = $mirror->GetMethod('ValidatorName')->Invoke(NULL);
                if ($validator_name == $this->input->model) {
                    if (is_array($this->input->data)) {
                        $this->model = $mirror->NewInstance(NULL);
                        $this->model->SetFrom($this->input->data);
                    } else {
                        $this->model = $mirror->NewInstance($this->input->data);
                    }
                    break;
                }
            }
        }
        if (!$this->model) {
            $this->_Error(-2, 'The model could not be created');
            return $this->Cancel();
        }

        // Make sure the validator exists.
        $this->validator = $this->model->GetValidator();
        if (!$this->validator || !$this->validator instanceof ModelValidator) {
            $this->_Error(-3, 'The validator could not be created');
            return $this->Cancel();
        }

        // Perform access checks.
        if (!$this->validator->FilterAction($this)) {
            $this->_Error(-4, 'No permission to access this record');
            return $this->Cancel();
        }

        // Only certain actions require validation (those that alter records).
        if (in_array($this->input->action,
                     array(self::ACTION_VALIDATE,
                           self::ACTION_INSERT,
                           self::ACTION_UPDATE))) {
            if (!$this->_Validate())
                return $this->Cancel();
        }

        // Perform the actual action.
        try {
            switch ($this->input->action) {
                case self::ACTION_FETCH:
                    $this->record = $this->model->Fetch();
                    return;
                case self::ACTION_DELETE:
                    $this->model->Delete();
                    return;
                case self::ACTION_VALIDATE:
                    // This is taken care of with the above validation.
                    return;
                case self::ACTION_INSERT:
                    $this->model->Insert();
                    $this->record = $this->model->Fetch();
                    return;
                case self::ACTION_UPDATE:
                    $this->model->Update();
                    $this->record = $this->model->Fetch();
                    return;
                default:
                    $this->_Error(-6, 'Unhandled action');
                    $this->Cancel();
            }
        } catch (ModelException $e) {
            $this->_Error(-7, $e->GetMessage());
        }

        $this->model = NULL;
        $this->validator = NULL;
    }

    // Helper function for setting error state.
    private function _Error($code, $message)
    {
        if (!$this->errors)
            $this->errors = array();
        $this->code     = $code;
        $this->errors[] = $message;
        $this->record   = NULL;
        $this->Cancel();
    }


    private function _Validate()
    {
        $validator = $this->model->GetValidator();
        $validator->Validate();
        if (!$validator->is_valid()) {
            $this->code   = -5;
            $this->errors = $validator->errors();
            $this->record = NULL;
            $this->Cancel();
            return FALSE;
        }
        return TRUE;
    }
}
