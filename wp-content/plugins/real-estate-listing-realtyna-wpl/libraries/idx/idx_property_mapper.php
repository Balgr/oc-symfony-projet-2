<?php

defined('_WPLEXEC') or die('Restricted access');

_wpl_import('libraries.wpl_property_types');


class idx_property_mapper
{
   protected $preConfig = array(
        'name' => 'Property Title' ,
        'description' =>'Property Description',
        'num_beds' =>'Bedrooms',
        'num_baths'=> 'Bathrooms',
        'features'=> 'Features' ,
        'price_period' => 'Price Type' ,
        'property_type' =>'Property Type',
        'listing_type'=> 'Listing Type' ,
        'street_address'=>  'Street' ,
        'postal_code'=>'Postal Code',
        'price' => 'Price' ,
        'year_built' => 'Year Built'
     );
 


  public function map( array $fields)
  {
     $preparedKeys = $this->prepareKeys(array_keys($fields));
     $dbstValues = $this->getDbstValues( $preparedKeys );
     $dataToInsert = array();
     foreach ( $dbstValues as $key => $value ) {
        if (!empty($value)) {
           $dataToInsert[$value] = $fields[$key];
        }
     }
     $dataToInsert['listing'] = $this->getType(self::_('listing_type',$fields));
     $dataToInsert['property_type'] = $this->getType(self::_('property_type',$fields),'property');
     $dataToInsert['mls_id'] = self::_('listing_id',$fields);
     return $dataToInsert;
   }

   protected function getType( $String, $type = 'listing' )
   {
     $id = '';
     if ($type == 'listing') {
       foreach (wpl_listing_types::get_listing_types() as $i => $value) {
        if (self::_('name',$value) == $String) {
           $id = self::_('id',$value);
           break;
        }
      }
     }elseif ($type == 'property') {
     	$query = wpl_db::select("SELECT * FROM `#__wpl_property_types`", 'loadAssocList');

        foreach ($query as $i => $value) {
        if (self::_('name',$value) == $String) {
           $id = self::_('id',$value);
           break;
         }
       }
     }
      return $id;
   }


   protected function getDbstValues ( $vArr )
   {
     $matchedList = array();
     foreach ( $vArr as $key => $name ) {
       $query = wpl_db::select("SELECT `name`,`table_name`,`table_column` FROM `#__wpl_dbst` WHERE `name` = '{$name}' AND `enabled` != 0 ", 'loadAssoc');
       
       if (is_null($query)) {
       	$anotherTry = self::_($key,array_reverse($this->preConfig));
         if (!empty($anotherTry)) {
         	 $query = wpl_db::select("SELECT `name`,`table_name`,`table_column` FROM `#__wpl_dbst` WHERE `name` = '{$anotherTry}' AND `enabled` != 0", 'loadAssoc');
          }
       } 
        $matchedList[$key] = self::_('table_column',$query);
     } 
      return $matchedList;
   }

   protected function prepareKeys( array $kArr)
   {  
      
      $pKeys = array();

      foreach ( $kArr as $string ) {
        $strArr = explode('_', $string);

        if ( count($strArr) > 1 ) {
           $pKeys[$string] = implode(' ', array_map(function($k) {
             return ucfirst($k);
           },$strArr));
            continue;
         }
         $pKeys[$string] = ucfirst($string); 
      }

      return $pKeys;
   }


   protected function saveExternalImages( $imgs,$pid,$db )
   {

    if (is_array($imgs) && count($imgs) > 0 ) {
        foreach( $imgs as $img ) {
            $db->insert($db->prefix.'wpl_items',array(
					'parent_id'     => $pid,
					'creation_date' => date("Y-m-d H:i:s"),
					'item_type'     => 'gallery',
					'item_cat'      => 'external',
					'item_name'     => 'external_image'.$pid,
					'item_extra3'   => $img
				));
        }

    }
    
   }
   
   protected function setFeatures($featured,$pid,$db)
   {
       if (is_array($featured) && count($featured) > 0 ) {
          foreach ($featured as $ft) {
              
              $query = wpl_db::select("SELECT `table_column` FROM `#__wpl_dbst` WHERE `name` = '{$ft}' AND `type`='feature' AND `enabled` != 0", 'loadAssoc');
              
              $columnName = self::_('table_column',$query);

              if (is_null($query) || empty($columnName)) {
                 continue;
              } 
              
              $db->update($db->prefix.'wpl_properties',array(
                $columnName => 1 
              ),array(
                 'id' => $pid
              ));

          }
       
       }
   }

   protected function setPricePeriod($pricePeriod,$pid,$db)
   {
      if (is_string($pricePeriod) && !empty($pricePeriod)) {
        $query = wpl_db::select("SELECT `options` FROM `#__wpl_dbst` WHERE `table_column` = 'price_period'", 'loadAssoc');

        $options = json_decode(self::_('options',$query),true);

        if (isset($options['params'])) {
             foreach ($options['params'] as $param) {
              if (self::_('value',$param) == $pricePeriod && self::_('enabled',$param) == 1) {

              	 $insertArray = array(
                       'price_period' => $param['key'] 
                  );

              	 $db->update($db->prefix.'wpl_properties',$insertArray,array(
	                 'id' => $pid
	              ));
                  

                  break;
               }
             }
          }

      }
   }

   public function getLotAreaUnitId( $lUnit,$pid,$db )
   {
      if (is_string($lUnit) && !empty($lUnit)) {
          $query = wpl_db::select("SELECT * FROM `#__wpl_units`
			 WHERE `enabled` = 1 and `name` = '{$lUnit}' and `type` = 2 ", 'loadAssoc');
          if (is_null($query)) {
              return;
          }

          $insertArray = array(
            'lot_area_unit' => $query['id']
          );
         
         $db->update($db->prefix.'wpl_properties',$insertArray,array(
	                 'id' => $pid
	      ));


      }
   }

   protected function setLocations($adr,$pid,$db)
   {
        if (is_array($adr) && count($adr) > 0 ) {
          $knownLocationsKeywords = array();
          $allowedArr = array('country','state','county','city');
          foreach ( $adr as $k => $v ) {
             
             if (!in_array($k, $allowedArr)) {
               continue;
             } 

             $query = wpl_db::select("SELECT `setting_name`,`setting_value` FROM `#__wpl_settings` WHERE `setting_value` = '".ucfirst($k)."'", 'loadAssoc'
            );

             if (!is_null($query)) {

               $knownLocationsKeywords[str_replace('_keyword', '_name', self::_('setting_name',$query))] = $v;
               unset($adr[$k]);

             }

          }
          
           $prepareLeft = $this->prepareKeys(array_keys($adr));
           $leftDbStVals = $this->getDbstValues($prepareLeft);
           
           foreach ($leftDbStVals as $kkk => $vvv) {
               $knownLocationsKeywords[$vvv] = $adr[$kkk];
           } 
           
           if(count($knownLocationsKeywords) ) {
               $db->update($db->prefix.'wpl_properties',$knownLocationsKeywords,array(
                 'id' => $pid
               ));
            }

          }
   }
   

  protected function mapPTypes(array $pTypes)
  {
      $currentPTypes = wpl_db::select("SELECT * FROM `#__wpl_property_types`", 'loadAssocList');

      $pNames = array_map(function ($pIndex) {
         return self::_('name',$pIndex); 
      }, $currentPTypes);
      

      foreach ( $pTypes as $k => $propertyType ) {
         if (!in_array($propertyType,$pNames)) {
            $parentId = self::getPropertyParentId($currentPTypes,$propertyType);
            wpl_property_types::insert_property_type($parentId,$propertyType);           
         }
      } 
      
  }

  private static function getPropertyParentId(array $currentPTypes,$pName)
  {
     
     $pId = 1;

     if (!count($currentPTypes)) {
        return $pId;
     }
     
     foreach ( $currentPTypes as $k => $v ) {
        $expectedPropertyParent = self::_('0',explode(' ',$pName));
        
        if ($expectedPropertyParent == $v['name']) {
            $pId = $v['id'];
            break;
        }
     }

     return $pId; 

  }




  protected static function _($Key, $Collection, $Default = '') {
       $Keys = explode('.', $Key);
       $Data = $Collection;

        foreach ($Keys as $kkk) {
           if (is_object($Data)) {

                $Data = (array) $Data;
            }
            if (!isset($Data[$kkk])) {
                return $Default;
            }

            $Data = $Data[$kkk];
        }
        return $Data;
    }

}