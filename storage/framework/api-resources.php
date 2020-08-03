<?php return array (
  'menus' => 
  array (
    'controller' => 'Igniter\\Local\\Resources\\Menus',
    'only' => 
    array (
      0 => 'index',
      1 => 'store',
      2 => 'show',
      3 => 'update',
      4 => 'destroy',
    ),
    'middleware' => 
    array (
      0 => 'api',
    ),
    'prefix' => 'v2',
  ),
  'categories' => 
  array (
    'controller' => 'Igniter\\Local\\Resources\\Categories',
    'only' => 
    array (
      0 => 'index',
      1 => 'store',
      2 => 'show',
      3 => 'update',
      4 => 'destroy',
    ),
    'middleware' => 
    array (
      0 => 'api',
    ),
    'prefix' => 'v2',
  ),
  'locations' => 
  array (
    'controller' => 'Igniter\\Local\\Resources\\Locations',
    'only' => 
    array (
      0 => 'index',
      1 => 'store',
      2 => 'show',
      3 => 'update',
      4 => 'destroy',
    ),
    'middleware' => 
    array (
      0 => 'api',
    ),
    'prefix' => 'v2',
  ),
);