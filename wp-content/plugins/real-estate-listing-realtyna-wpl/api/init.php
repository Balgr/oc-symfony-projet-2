<?php
// API initializer script will go here. We should remove this comment.

add_action( 'rest_api_init', function () {
   // Register Routes Here;

  register_rest_route( '/idx_api/v1','import/(?P<token>[a-zA-Z0-9-]+)', array(
       'methods'  => 'POST',
            'callback' => function(\WP_REST_Request $request) {
                 _wpl_import('libraries.idx.addon_idxn');
                 
                 (new addon_idxn)
                       ->import($request);
            },
           'args' => array(
                'listing_type' => array(
                    'required' => true,
                    'type' => 'string'
                ),
                'property_type' => array(
                    'required' => true,
                    'type' => 'string'
                ),
                'description' => array(
	                'required' => true,
	                'type' => 'string'
                ),
                'price' => array(
                    'required' => true,
                    'type' => 'number',
                ),
                'price_unit' => array(
                    'required' => true,
                    'type' => 'string'
                ),
                'price_period' => array(
                    'required' => false,
                    'type' => 'string'
                ),
                'bedrooms' => array(
                    'required' => false,
                    'type' => 'number'
                ),
                'bathrooms' => array(
                    'required' => false,
                    'type' => 'number'
                ),
                'half_bathrooms' => array(
                    'required' => false,
                    'type' => 'number'
                ),
                'square_footage' => array(
                    'required' => false,
                    'type' => 'number'
                ),
                'square_footage_unit' => array(
                    'required' => false,
                    'type' => 'string'
                ),
                'lot_area' => array(
                    'required' => false,
                    'type' => 'number'
                ),
                'lot_area_unit' => array(
                    'required' => false,
                    'type' => 'string'
                ),
                'year_built' => array(
                    'required' => false,
                    'type' => 'integer'
                ),
                'address' => array(
                    'required' => true,
                    'type' => 'array',
                ),
                'features' => array(
	                'required' => false,
	                'type' => 'array',
                ),
                'image' => array(
                    'required' => true,
                    'type' => 'array',
                )
            )
  ));
});