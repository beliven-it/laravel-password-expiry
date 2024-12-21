<?php

namespace Beliven\PasswordExpiry\Tests;

use Beliven\PasswordExpiry\Traits\HasPasswordExpiration;
use Illuminate\Database\Eloquent\Model;

uses(TestCase::class)->in(__DIR__);

class TestModel extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'test_models';
}

class TestModelWithTrait extends Model
{
    use HasPasswordExpiration;

    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'test_models';
}
