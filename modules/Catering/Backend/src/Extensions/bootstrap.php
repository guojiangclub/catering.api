<?php

use Encore\Admin\Form;
use GuoJiangClub\Catering\Backend\Extensions\WangEditor;
use GuoJiangClub\Catering\Backend\Extensions\UEditor;

//Form::extend('editor', WangEditor::class);
Form::extend('editor', UEditor::class);