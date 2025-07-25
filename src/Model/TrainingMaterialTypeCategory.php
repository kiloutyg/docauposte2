<?php

namespace App\Model;

enum TrainingMaterialTypeCategory: string
{
    case UPLOAD = 'Upload';
    case WORKSTATION = 'Workstation';
    case OTHER = 'Other';
}
