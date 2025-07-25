<?php

namespace App\Model;

enum TrainingMaterialTypeCategory: string
{
    case UPLOAD = 'Specific Upload';
    case WORKSTATION = 'Workstation Upload';
    case SOMETHING_ELSE = 'Something Else';
}
