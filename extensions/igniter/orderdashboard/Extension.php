<?php namespace Igniter\OrderDashboard;

use Event;
use View;
use System\Classes\BaseExtension;
use Igniter\OrderDashboard\Controllers\Overview as OrderDashboardController;
use Illuminate\Foundation\AliasLoader;
use Admin\Controllers\Orders as AdminOrdersController;
use Admin\Controllers\Reviews as AdminReviewsController;
use Admin\Controllers\Statuses as AdminStatusesController;
use Admin\Controllers\Payments as AdminPaymentsController;

/**
 * OrderDashboard Extension Information File
 */
class Extension extends BaseExtension
{
  
    /**
     * Register method, called when the extension is first registered.
     *
     * @return void
     */
    public function register()
    {
        // https://packagist.org/packages/barryvdh/laravel-dompdf
        $this->app->register(\Barryvdh\DomPDF\ServiceProvider::class); 
        AliasLoader::getInstance()->alias('PDF', \Barryvdh\DomPDF\Facade::class);   

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $extensionViewPath = '\extensions\igniter\orderdashboard\views';
        } else {
            $extensionViewPath = '/extensions/igniter/orderdashboard/views';
        }
        
        $finder = new \Illuminate\View\FileViewFinder(app()['files'], array(base_path().$extensionViewPath));        
        View::setFinder($finder);
        
        
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {
       Event::listen('admin.controller.beforeResponse', function ($controller, $action, $params) {

            // if ($controller instanceof OrderDashboardController
            //     || $controller instanceof AdminOrdersController
            //     || $controller instanceof AdminReviewsController
            //     || $controller instanceof AdminStatusesController
            //     || $controller instanceof AdminPaymentsController) {
            //         $controller->addCss('$/igniter/orderdashboard/assets/css/orderdashboard.css', 'orderdashboard-css');
            //     }
                $controller->addCss('$/igniter/orderdashboard/assets/css/orderdashboard.css', 'orderdashboard-css');
                return;

            
        });
    }

    /**
     * Registers any front-end components implemented in this extension.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Igniter\OrderDashboard\Components\PreviewOrder' => [
                'code' => 'previewOrder',
                'name' => 'Order Preview',
                'description' => 'Order preview component'
            ]
        ];

    }

    /**
     * Registers any admin permissions used by this extension.
     *
     * @return array
     */
    public function registerPermissions()
    {
// Remove this line and uncomment block to activate
        return [
        //    'Igniter.OrderDashboard.SomePermission' => [
        //        'description' => 'Some permission',
        // //        'group' => 'module',
        //    ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'sales' => [
                'child' => [
                    // 'overview' => [
                    //     'priority' => 5,
                    //     'href' => admin_url('igniter/orderdashboard/overview'),
                    //     'class' => 'overview',
                    //     'title' => 'Overview',
                    //     //'permission' => 'Igniter.OrderDashboard',
                    // ],
                    'grouped' => [
                        'priority' => 6,
                        'href' => admin_url('igniter/orderdashboard/groupedorders'),
                        'class' => 'grouped',
                        'title' => 'Grouped Orders',
                        //'permission' => 'Igniter.OrderDashboard',
                    ]
                ],
                
            ],
        ];
    }

}
