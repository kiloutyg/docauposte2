<?php

namespace App\Model;

enum TrainingMaterialTypeCategory: string
{
    case UPLOAD = 'Specific Upload';
    case WORKSTATION = 'Workstation';
    case OTHER = 'Other';
}
