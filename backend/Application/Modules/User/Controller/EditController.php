<?php

namespace Application\Modules\User\Controller;

use \Framework\Api;
use \Framework\Core\Globals\Get;
use \Framework\Core\Globals\Post;
use \Application\Models\Users;
use \Application\Models\UsersInfo;
use \Application\Models\Locations;
use \Application\Models\LocationCountries;
use \Application\Models\LocationCountriesTR;
use \Application\Models\LocationStates;
use \Application\Models\LocationStatesTR;
use \Application\Models\LocationCities;
use \Application\Models\LocationCitiesTR;
use \Application\Models\Entities;

class EditController extends \Framework\Core\Mvc\Controller{
    
    public function accountAction(){
        $auth = Api::app()->getObjectManager()->get('authentication');
        if($auth->isGuest())
            return $this->redirect();
        $userModel = Users::model();
        $usersInfoModel = UsersInfo::model();
        $locationModel = Locations::model();
        $user = $userModel->join('usersInfo', 
                                 'left', 
                                 'usersInfo.userID = users.id', 
                                 $usersInfoModel->getFields())
                            ->where(array('users.id' => $auth->getUserID()))
                            ->run();
        $location = $locationModel->where(array('entityID' => $auth->getUserID(), 'entityType' => Entities::ENTITY_TYPE_USER))->limit(1)->run();
        if(!empty($location))
            $this->assign('location', $location[0]);
        else
            $this->assign('location', null);
        $this->assign('user', $user[0]);
        $this->render('index'); 
    }

    /**
     * Render personal Information form
     */
    public function personalAction(){
        $auth = Api::app()->getObjectManager()->get('authentication');
        if($auth->isGuest())
            return false;
        $userModel = Users::model();
        $usersInfoModel = UsersInfo::model();
        $locationModel = Locations::model();
        $locationCountriesModel = LocationCountries::model();
        $locationCountriesTRModel = LocationCountriesTR::model();
        $locationStatesModel = LocationStates::model();
        $locationStatesTRModel = LocationStatesTR::model();
        $locationCitiesModel = LocationCities::model();
        $locationCitiesTRModel = LocationCitiesTR::model();

        $data = Post::str('data');
        if($data){
            $data = json_decode($data);
            if(isset($data->cancel)){
                return $this->renderPartial('personal');
            }
            //User Model
            $userObj = $userModel->getEmptyObject();
            $userObj->id = $auth->getUserID();
            $userObj->displayName = $data->displayName;
            $userObj->email = $data->email;
            $userModel->save($userObj);
            //UsersInfo model
            $usersInfoObj = $usersInfoModel->getEmptyObject();
            $usersInfoObj->userID = $auth->getUserID();
            $usersInfoObj->birthday = new \Datetime($data->year . '-' . $data->month . '-' . $data->day);
            if($data->gender == UsersInfo::USERSINFO_GENDER_MALE || $data->gender == UsersInfo::USERSINFO_GENDER_FEMALE)
                $usersInfoObj->gender = $data->gender;
            if(strlen($data->website) > 0)
                $usersInfoObj->website = $data->website;
            $usersInfoModel->save($usersInfoObj, array(), true); //Update
            //Location Country
            $countryID = $data->country;
            //Location States
            if(strlen($data->state) > 0){
                $locationState = $locationStatesTRModel->where(array('name' => $data->state, 'language' => Api::app()->language))->limit(1)->run();
                if(empty($locationState)){
                    $locationStateObj = $locationStatesModel->getEmptyObject();
                    $locationStateObj->countryID = $countryID;
                    $locationStateObj->isVerified = LocationStates::LOCATION_STATE_VERIFIED_TRUE;
                    $stateID = $locationStatesModel->save($locationStateObj); 
                    //Location State TR
                    $locationStateTRObj = $locationStatesTRModel->getEmptyObject();
                    $locationStateTRObj->stateID = $stateID;
                    $locationStateTRObj->language = Api::app()->language;
                    $locationStateTRObj->name = $data->state;
                    $locationStatesTRModel->save($locationStateTRObj); 
                }
                else{
                    $stateID = $locationState[0]->stateID;
                }
            }
            else
                $stateID = 0;

            //Location Cities
            if(strlen($data->city) > 0 && $stateID > 0){
                $locationCity = $locationCitiesTRModel->where(array('name' => $data->city, 'language' => Api::app()->language))->limit(1)->run();
                if(empty($locationCity)){
                    $locationCityObj = $locationCitiesModel->getEmptyObject();
                    $locationCityObj->countryID = $countryID;
                    $locationCityObj->stateID = $stateID;
                    $locationCityObj->isVerified = LocationCities::LOCATION_CITY_VERIFIED_TRUE;
                    $cityID = $locationCitiesModel->save($locationCityObj); 
                    //Location City TR
                    $locationCityTRObj = $locationCitiesTRModel->getEmptyObject();
                    $locationCityTRObj->cityID = $cityID;
                    $locationCityTRObj->language = Api::app()->language;
                    $locationCityTRObj->name = $data->city;
                    $locationCitiesTRModel->save($locationCityTRObj); 
                }
                else{
                    $cityID = $locationCity[0]->cityID;
                }
            }
            else
                $cityID = 0;
            //Locations
            $userLocation = $locationModel->where(array('entityType' => Entities::ENTITY_TYPE_USER, 'entityID' => $auth->getUserID()))->limit(1)->run();
            $locationObj = $locationModel->getEmptyObject();
            if(empty($userLocation)){
                $locationObj->entityType = Entities::ENTITY_TYPE_USER;
                $locationObj->entityID = $auth->getUserID();
                $locationObj->countryID = $countryID;
                $locationObj->stateID = $stateID;
                $locationObj->cityID = $cityID;
                if(strlen($data->address) > 0)
                    $locationObj->address = $data->address;
                if(strlen($data->zip))
                    $locationObj->zip = $data->zip;
                if(strlen($data->fax))
                    $locationObj->fax = $data->fax;
                if(strlen($data->phoneNumber))
                    $locationObj->mobilePhone1 = $data->phoneNumber;
                $locationModel->save($locationObj);
            }
            else{
                $isExistParams = 0;
                if($userLocation[0]->countryID != $countryID){
                    $locationObj->countryID = $countryID;
                    $isExistParams++;
                }
                if($userLocation[0]->stateID != $stateID){
                    $locationObj->stateID = $stateID;
                    $isExistParams++;
                }
                if($userLocation[0]->cityID != $cityID){
                    $locationObj->cityID = $cityID;
                    $isExistParams++;
                }
                if($userLocation[0]->fax != $data->fax){
                    $locationObj->fax = $data->fax;
                    $isExistParams++;
                }
                if($userLocation[0]->zip != $data->zip){
                    $locationObj->zip = $data->zip;
                    $isExistParams++;
                }
                if($userLocation[0]->mobilePhone1 != $data->phoneNumber){
                    $locationObj->mobilePhone1 = $data->phoneNumber;
                    $isExistParams++;
                }
                if($userLocation[0]->address != $data->address){
                    $locationObj->address = $data->address;
                    $isExistParams++;
                }
                if($isExistParams > 0){
                    $locationObj->id = $userLocation[0]->id;
                    $locationModel->save($locationObj);
                }
            }
            return $this->renderPartial('personal');
        }
        else{
            $user = $userModel->join('usersInfo', 
                                     'left', 
                                     'usersInfo.userID = users.id', 
                                     $usersInfoModel->getFields())
                            ->where(array('users.id' => $auth->getUserID()))
                            ->run();
            $location = $locationModel->where(array('entityType' => Entities::ENTITY_TYPE_USER,
                                                    'entityID' => $user[0]->id))
                                        ->limit(1)
                                        ->run(); 
            if(!empty($location)){
                $locationStatesTRModel = LocationStatesTR::model();
                $locationState = $locationStatesTRModel->where(array('stateID' => $location[0]->stateID, 
                                                                     'language' => Api::app()->language))
                                                        ->limit(1)
                                                        ->run();
                $locationCitiesTRModel = LocationCitiesTR::model();
                $locationCity = $locationCitiesTRModel->where(array('cityID' => $location[0]->cityID, 
                                                                    'language' => Api::app()->language))
                                                        ->limit(1)
                                                        ->run();
            }
            $allLocationCountries = $locationCountriesTRModel->where(array('language' => Api::app()->language))->run();


            if(!empty($location))
                $this->assign('location', $location[0]);
            if(!empty($locationState))
                $this->assign('locationState', $locationState[0]);
            if(!empty($locationCity))
                $this->assign('locationCity', $locationCity[0]);
            $this->assign('allCountries', $allLocationCountries); 
            $this->assign('user', $user[0]);
            $this->assign('isForm', true);
            $this->renderPartial('personal');
        }
    }


}







