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

namespace phalanx\test;
use \phalanx\base\Dictionary;
use \phalanx\data as data;
use \phalanx\tasks\TaskPump;

class ValidatingTestModelValidator extends data\ModelValidator
{
    static public $validate_title_return_value = TRUE;
    protected function _validate_title($value)
    {
        return self::$validate_title_return_value;
    }

    static public $validate_description_generates_error = FALSE;
    protected function _validate_description($value)
    {
        if (!self::$validate_description_generates_error)
            return TRUE;
        $this->_ValidationError('Description error');
        return FALSE;
    }

    protected function _validate_value($value)
    {
        return TRUE;
    }

    static public $validate_is_hidden_generates_errors = FALSE;
    protected function _validate_is_hidden($value)
    {
        if (!self::$validate_is_hidden_generates_errors)
            return TRUE;
        $this->_ValidationError('Error 1');
        $this->_ValidationError('Error 2');
        return FALSE;
    }

    protected function _validate_reference_id($value)
    {
        return TRUE;
    }

    // Override.
    protected function _OnInitialize()
    {
        parent::_OnInitialize();  // For test coverage numbers.
        $this->T_set_error_on_unvalidated_key(FALSE);
    }

    // Test functions.
    public function T_set_error_on_unvalidated_key($flag)
    {
        $this->error_on_unvalidated_key = $flag;
    }
}

class ValidatingTestModel extends TestModel implements data\ValidatingModel
{
    public function GetValidator()
    {
        return new ValidatingTestModelValidator($this);
    }

    static public function ValidatorName()
    {
        return 'test_model';
    }
}

class ValidatingModelTest extends \PHPUnit_Framework_TestCase
{
    public $db;

    public $model;
    public $validator;

    public function setUp()
    {
        $this->db = ValidatingTestModel::SetUpDatabase();
        ValidatingTestModel::set_db($this->db);
        $this->assertSame($this->db, ValidatingTestModel::db());

        $this->model = new ValidatingTestModel();
        $this->validator = $this->model->GetValidator();

        ValidatingTestModelValidator::$validate_title_return_value = TRUE;
        ValidatingTestModelValidator::$validate_description_generates_error = FALSE;
        ValidatingTestModelValidator::$validate_is_hidden_generates_errors = FALSE;
    }

    public function testErrorOnUnvalidatedKey()
    {
        $this->setExpectedException('phalanx\data\ModelValidatorException');
        $this->validator->T_set_error_on_unvalidated_key(TRUE);
        $this->validator->Validate();
        $this->assertFalse($this->validator->is_valid());
    }

    public function testNoErrorOnUnvalidatedKey()
    {
        $this->validator->Validate(); 
        $this->assertTrue($this->validator->is_valid());
    }

    public function testInvalid()
    {
        ValidatingTestModelValidator::$validate_title_return_value = FALSE;
        $this->validator->Validate();
        $this->assertFalse($this->validator->is_valid());
    }

    public function testInvalidWithError()
    {
        ValidatingTestModelValidator::$validate_description_generates_error = TRUE;
        $this->validator->Validate();
        $this->assertFalse($this->validator->is_valid());
        $expected = array(
            'description' => array('Description error')
        );
        $this->assertEquals($this->validator->errors(), $expected);
    }

    public function testInvalidWithMultipleErrors1()
    {
        ValidatingTestModelValidator::$validate_is_hidden_generates_errors = TRUE;
        $this->validator->Validate();
        $this->assertFalse($this->validator->is_valid());
        $expected = array(
            'is_hidden' => array('Error 1', 'Error 2')
        );
        $this->assertEquals($this->validator->errors(), $expected);
    }

    public function testInvalidWithMultipleErrors2()
    {
        ValidatingTestModelValidator::$validate_description_generates_error = TRUE;
        ValidatingTestModelValidator::$validate_is_hidden_generates_errors = TRUE;
        $this->validator->Validate();
        $this->assertFalse($this->validator->is_valid());
        $expected = array(
            'description' => array('Description error'),
            'is_hidden' => array('Error 1', 'Error 2')
        );
        $this->assertEquals($this->validator->errors(), $expected);
    }
}

class ValidatingModelTaskTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->db = ValidatingTestModel::SetUpDatabase();
        ValidatingTestModel::set_db($this->db);
        $this->assertSame($this->db, ValidatingTestModel::db());

        ValidatingTestModelValidator::$validate_title_return_value = TRUE;
        ValidatingTestModelValidator::$validate_description_generates_error = FALSE;
        ValidatingTestModelValidator::$validate_is_hidden_generates_errors = FALSE;
    }

    public function testInvalidAction()
    {
        $data  = new Dictionary(array(
            'model'  => 'test_model',
            'action' => '__invalid__'
        ));
        $task = new data\ValidatingModelTask($data);
        TaskPump::Pump()->RunTask($task);
        $this->assertNotEquals(0, $task->code());
        $this->assertEquals(1, count($task->errors()));
    }

    public function testInvalidModel()
    {
        $data = new Dictionary(array(
            'model'  => '__invalid__',
            'action' => data\ValidatingModelTask::ACTION_FETCH
        ));
        $task = new data\ValidatingModelTask($data);
        TaskPump::Pump()->RunTask($task);
        $this->assertNotEquals(0, $task->code());
        $this->assertEquals(1, count($task->errors()));
    }

    public function testVaidFetch()
    {
        $data = new Dictionary(array(
            'model'  => 'test_model',
            'action' => data\ValidatingModelTask::ACTION_FETCH,
            'data'   => NULL
        ));

        // Insert the record first.
        $obj = new TestModel();
        $record = array(
            'title'        => 'foo',
            'description'  => 'bar',
            'value'        => 'baz',
            'is_hidden'    => TRUE,
            'reference_id' => 3
        );
        $obj->SetFrom($record);
        $obj->Insert();

        $data->data = $obj->id;

        $task = new data\ValidatingModelTask($data);
        TaskPump::Pump()->RunTask($task);

        $this->assertEquals(0, $task->code());
        $actual = $task->record();
        $this->assertNotNull($actual->id);

        foreach ($record as $key => $value) {
            $this->assertNotNull($actual->$key);
        }
    }

    public function testInvalidFetch()
    {
        $data = new Dictionary(array(
            'model'  => 'test_model',
            'action' => data\ValidatingModelTask::ACTION_FETCH,
            'data'   => 1239823
        ));

        $task = new data\ValidatingModelTask($data);
        TaskPump::Pump()->RunTask($task);

        $this->assertNotEquals(0, $task->code());
        $this->assertEquals(1, count($task->errors()));
        $this->assertNull($task->record());
    }

    public function testDelete()
    {
        $data = new Dictionary(
            'model', 'test_model',
            'action', data\ValidatingModelTask::ACTION_DELETE
        );

        $record = new TestModel();
        $record->SetFrom(array(
            'title' => 'foo'
        ));
        $record->Insert();

        $data->data = $record->id;

        $task = new data\ValidatingModelTask($data);
        TaskPump::Pump()->RunTask($task);

        $this->assertEquals(0, $task->code());

        try {
            $obj = new TestModel($record->id);
            $obj->Fetch();
            $this->fail('Did not delete record');
        } catch (data\ModelException $e) {
            // Success.
        }
    }

    public function testValidateValid()
    {
        $data = new Dictionary(
            'model', 'test_model',
            'action', data\ValidatingModelTask::ACTION_VALIDATE,
            'data', array(
                'title'       => 'Moo',
                'description' => 'baaa'
            )
        );

        $task = new data\ValidatingModelTask($data);
        TaskPump::Pump()->RunTask($task);

        $this->assertEquals(0, $task->code());
        $this->assertEquals(0, count($task->errors()));
        $this->assertNull($task->record());
    }

    public function testValidateInvalid1Error()
    {
        $data = new Dictionary(
            'model', 'test_model',
            'action', data\ValidatingModelTask::ACTION_VALIDATE,
            'data', array(
                'title'       => 'Moo',
                'description' => 'baaa'
            )
        );

        ValidatingTestModelValidator::$validate_description_generates_error = TRUE;

        $task = new data\ValidatingModelTask($data);
        TaskPump::Pump()->RunTask($task);

        $this->assertNotEquals(0, $task->code());
        $this->assertNull($task->record());

        $errors = $task->errors();
        $this->assertEquals(1, count($errors));        
        $this->assertNotNull($errors['description']);
        $this->assertEquals(1, count($errors['description']));
    }

    public function testValidateInvalid2Errors()
    {
        $data = new Dictionary(
            'model', 'test_model',
            'action', data\ValidatingModelTask::ACTION_VALIDATE,
            'data', array(
                'title'       => 'Moo',
                'description' => 'baaa'
            )
        );

        ValidatingTestModelValidator::$validate_is_hidden_generates_errors = TRUE;

        $task = new data\ValidatingModelTask($data);
        TaskPump::Pump()->RunTask($task);

        $this->assertNotEquals(0, $task->code());
        $this->assertNull($task->record());

        $errors = $task->errors();
        $this->assertEquals(1, count($errors));        
        $this->assertNotNull($errors['is_hidden']);
        $this->assertEquals(2, count($errors['is_hidden']));
    }

    public function testValidateInvalid2Errors2Fields()
    {
        $data = new Dictionary(
            'model', 'test_model',
            'action', data\ValidatingModelTask::ACTION_VALIDATE,
            'data', array(
                'title'       => 'Moo',
                'description' => 'baaa'
            )
        );

        ValidatingTestModelValidator::$validate_description_generates_error = TRUE;
        ValidatingTestModelValidator::$validate_is_hidden_generates_errors = TRUE;

        $task = new data\ValidatingModelTask($data);
        TaskPump::Pump()->RunTask($task);

        $this->assertNotEquals(0, $task->code());
        $this->assertNull($task->record());

        $errors = $task->errors();
        $this->assertEquals(2, count($errors));        
        $this->assertNotNull($errors['description']);
        $this->assertEquals(1, count($errors['description']));
        $this->assertNotNull($errors['is_hidden']);
        $this->assertEquals(2, count($errors['is_hidden']));
    }

    public function testValidInsert()
    {
        $data = new Dictionary(
            'model', 'test_model',
            'action', data\ValidatingModelTask::ACTION_INSERT,
            'data', array(
                'title'       => 'Moo',
                'description' => 'baaa'
            )
        );

        $task = new data\ValidatingModelTask($data);
        TaskPump::Pump()->RunTask($task);

        $this->assertEquals(0, $task->code());
        $this->assertEquals(0, count($task->errors()));

        $record = $task->record();
        $this->assertEquals($data->{'data.title'}, $record->title);
        $this->assertEquals($data->{'data.description'}, $record->description);
        $this->assertNotEquals(0, $record->id);
    }

    public function testInvalidInsert()
    {
        $data = new Dictionary(
            'model', 'test_model',
            'action', data\ValidatingModelTask::ACTION_INSERT,
            'data', array(
                'title'       => 'Moo',
                'description' => 'baaa'
            )
        );

        ValidatingTestModelValidator::$validate_description_generates_error = TRUE;

        $task = new data\ValidatingModelTask($data);
        TaskPump::Pump()->RunTask($task);

        $this->assertNotEquals(0, $task->code());
        $this->assertNull($task->record());
        $errors = $task->errors();
        $this->assertEquals(1, count($errors));
        $this->assertEquals(1, count($errors['description']));
    }

    public function testValidUpdate()
    {
        $record = new TestModel();
        $record->SetFrom(array(
            'title'       => 'Moo',
            'description' => 'foo'
        ));
        $record->Insert();

        $data = new Dictionary(
            'model', 'test_model',
            'action', data\ValidatingModelTask::ACTION_UPDATE,
            'data', array(
                'id'    => $record->id,
                'title' => 'bar'
            )
        );

        $task = new data\ValidatingModelTask($data);
        TaskPump::Pump()->RunTask($task);

        $this->assertEquals(0, $task->code());
        $this->assertEquals(0, count($task->errors()));

        $actual = $task->record();
        $this->assertEquals($record->id, $actual->id);
        $this->assertEquals($data->{'data.title'}, $actual->title);
        $this->assertEquals($record->description, $actual->description);
    }

    public function testInvalidUpdate()
    {
        $record = new TestModel();
        $record->SetFrom(array(
            'title'       => 'Moo',
            'description' => 'foo'
        ));
        $record->Insert();

        $data = new Dictionary(
            'model', 'test_model',
            'action', data\ValidatingModelTask::ACTION_UPDATE,
            'data', array(
                'id'          => $record->id,
                'title'       => 'bar',
                'description' => 'baz'
            )
        );

        ValidatingTestModelValidator::$validate_description_generates_error = TRUE;

        $task = new data\ValidatingModelTask($data);
        TaskPump::Pump()->RunTask($task);

        $this->assertNotEquals(0, $task->code());
        $errors = $task->errors();
        $this->assertEquals(1, count($errors));
        $this->assertEquals(1, count($errors['description']));
        $this->assertNull($task->record());

        $actual = $record->Fetch();
        $this->assertEquals($record->id, $actual->id);
        $this->assertEquals($record->title, $actual->title);
        $this->assertEquals($record->description, $actual->description);
    }
}
