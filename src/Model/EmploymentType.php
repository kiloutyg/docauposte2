<?php

namespace App\Model;

enum EmploymentType: string
{
    case PERMANENT = 'permanent';
    case TEMPORARY = 'temporary';
    case CONTRACT = 'contract';
}
