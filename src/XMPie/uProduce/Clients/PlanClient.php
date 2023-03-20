<?php

namespace App\XMPie\uProduce\Clients;

use SoapFault;

class PlanClient extends BaseClient
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $id
     * @return bool|null
     * @throws SoapFault
     */
    public function isExist($id): ?bool
    {
        $Request = $this->RequestFabricator->Plan_SSP()
            ->IsExist()
            ->setInPlanID($id);
        $Service = $this->ServiceFabricator->Plan_SSP();
        $result = $Service->IsExist($Request);

        return $result->getIsExistResult();
    }

    /**
     * Validate the Plan by ID.
     * Will check that the current username/password can actually access the Plan
     * Will return the Plan ID or false
     *
     * You cannot validate a Plan Name as Plan Names are not unique
     *
     * @param int $id
     * @return int|false
     * @throws SoapFault
     */
    public function validate(int $id): bool|int
    {
        if ($this->isExist($id)) {
            try {
                $props = $this->getAllProperties($id);
                if (isset($props['planID'])) {
                    return intval($id);
                } else {
                    return false;
                }
            } catch (\Throwable $exception) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $id
     * @return string|null
     * @throws SoapFault
     */
    public function getName($id): ?string
    {
        $Request = $this->RequestFabricator->Plan_SSP()
            ->GetName()
            ->setInPlanID($id);
        $Service = $this->ServiceFabricator->Plan_SSP();
        $result = $Service->GetName($Request);

        return $result->getGetNameResult();
    }

    /**
     * @param $id
     * @return string[]|null
     * @throws SoapFault
     */
    public function getAllProperties($id): ?array
    {
        $Request = $this->RequestFabricator->Plan_SSP()
            ->GetAllProperties()
            ->setInPlanID($id);
        $Service = $this->ServiceFabricator->Plan_SSP();
        $result = $Service->GetAllProperties($Request);

        $properties = [];
        foreach ($result->getGetAllPropertiesResult() as $prop) {
            $properties[$prop->getM_Name()] = $prop->getM_Value();
        }

        return $properties;
    }

    /**
     * Get the Recipient Fields by Plan ID
     *
     * @param $planId
     * @return array
     * @throws SoapFault
     */
    public function getRecipientFields($planId): array
    {
        $Request = $this->RequestFabricator->PlanUtils_SSP()
            ->GetRecipientFields()
            ->setInPlanID($planId)
            ->setInTrivialPlan(false);
        $Response = $this->ServiceFabricator->PlanUtils_SSP()
            ->GetRecipientFields($Request);
        $fields = $Response->getGetRecipientFieldsResult();

        $return = [];
        foreach ($fields as $field) {
            $return[$field->getM_Name()] = [
                'name' => $field->getM_Name(),
                'comment' => $field->getM_Comment(),
                'type' => $field->getM_Type(),
                'is_primary' => $field->getM_IsPrimary(),
                'is_internal' => $field->getM_IsInternal(),
            ];
        }

        return $return;
    }

    /**
     * Get the ADORs by Plan ID
     *
     * @param $planId
     * @return array
     * @throws SoapFault
     */
    public function getADORs($planId): array
    {
        $Request = $this->RequestFabricator->PlanUtils_SSP()->GetADORs()->setInPlanID($planId)->setInIOType('RW')->setInTrivialPlan(false);
        $Response = $this->ServiceFabricator->PlanUtils_SSP()->GetADORs($Request);
        $adors = $Response->getGetADORsResult();
        try {
            $adorCount = $adors->count();
        } catch (\Throwable $exception) {
            $adorCount = 0;
        }

        $return = [];
        if ($adorCount > 0) {
            foreach ($adors as $ador) {
                $return[$ador->getM_Name()] = [
                    'name' => $ador->getM_Name(),
                    'comment' => $ador->getM_Comment(),
                    'type' => $ador->getM_Type(),
                    'extended_type' => $ador->getM_ExtendedType(),
                    'group' => $ador->getM_Group(),
                    'is_dial' => $ador->getM_IsDial(),
                    'read_expression' => $ador->getM_ReadExpression(),
                    'write_expression' => $ador->getM_WriteExpression(),
                ];
            }
        }

        return $return;
    }

    /**
     * Get the Variables by Plan ID
     *
     * @param $planId
     * @return array
     * @throws SoapFault
     */
    public function getVariables($planId): array
    {
        $Request = $this->RequestFabricator->PlanUtils_SSP()->GetVariables()->setInPlanID($planId)->setInIOType('RW')->setInTrivialPlan(false);
        $Response = $this->ServiceFabricator->PlanUtils_SSP()->GetVariables($Request);
        $variables = $Response->getGetVariablesResult();
        try {
            $variableCount = $variables->count();
        } catch (\Throwable $exception) {
            $variableCount = 0;
        }

        $return = [];
        if ($variableCount > 0) {
            foreach ($variables as $variable) {
                $return[$variable->getM_Name()] = [
                    'name' => $variable->getM_Name(),
                    'comment' => $variable->getM_Comment(),
                    'type' => $variable->getM_Type(),
                    'extended_type' => $variable->getM_ExtendedType(),
                    'group' => $variable->getM_Group(),
                    'is_dial' => $variable->getM_IsDial(),
                    'read_expression' => $variable->getM_ReadExpression(),
                    'write_expression' => $variable->getM_WriteExpression(),
                ];
            }
        }

        return $return;
    }
}