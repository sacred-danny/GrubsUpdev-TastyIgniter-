<?php namespace Igniter\OrderDashboard\Controllers;

use AdminMenu;
use Event;
use Admin\Traits\ListExtendable;

use Igniter\Flame\Exception\ApplicationException;
use Igniter\OrderDashboard\Models\Orders_model as OrderDashboardModel;
use Igniter\OrderDashboard\Controllers\Overview as OverviewController;

class GroupedOrders extends \Admin\Classes\AdminController
{

    use ListExtendable;

    protected $primaryAlias = 'groupedlist';

    private $modelConfig = [
        'orders' => 'Igniter\OrderDashboard\Models\Orders_model'
    ];

    public $implement = [
        'Admin\Actions\ListController',
        'Admin\Actions\FormController',
        'Admin\Actions\LocationAwareController',
        'Igniter\OrderDashboard\Actions\GroupedListController'       
    ];
     
    public $listConfig = [
        'groupedlist' => [
            'model' => 'Igniter\OrderDashboard\Models\Orders_model',
            'title' => 'Grouped Orders',
            'emptyMessage' => 'lang:admin::lang.orders.text_empty',
            'showCheckboxes' => FALSE,
            'defaultSort' => ['order_id', 'DESC'],
            'configFile' => 'grouped_orders_model',
        ],
    ];

    public $formConfig = [
        'name' => 'lang:admin::lang.orders.text_form_name',
        'model' => 'Igniter\OrderDashboard\Models\Orders_model',
        'request' => 'Admin\Requests\Order',
        'edit' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'orders/edit/{order_id}',
            'redirectClose' => 'orders',
        ],
        'preview' => [
            'title' => 'lang:admin::lang.form.preview_title',
            'redirect' => 'orders',
        ],
        'delete' => [
            'redirect' => 'orders',
        ],
        'configFile' => 'orders_model',
    ];

    protected $requiredPermissions = ['Admin.Orders', 'Admin.AssignOrders'];

    public function __construct()
    {
        parent::__construct();
        $alias = $this->primaryAlias;

        ///$listConfig = $this->getListConfig();

        // $modelClass = $listConfig['model'];
        // $model = new $modelClass;
        // unset($listConfig['model']);
        //$model = $this->listExtendModel($model, $alias);

        // Prep the list widget config
        // $requiredConfig = ['groupedlist'];
        // $configFile = $listConfig['configFile'];
        // $modelConfig = $this->loadConfig($configFile, $requiredConfig, 'groupedlist');

        // $columnConfig['columns'] = $modelConfig['columns'];
        // $columnConfig['model'] = $model;
        // $columnConfig['alias'] = $alias;


        //$widget = $this->makeWidget('Admin\formwidgets\StatusEditor', array());

        Event::listen('admin.list.extendQueryBefore', function($widget, $query) {
           $query->where('status_id', '<>', 5); // not equal to completed
        });

        AdminMenu::setContext('grouped', 'sales');            
        
    }

    /**
     * Returns the configuration used by this behavior.
     *
     * @param null $alias
     *
     * @return \Admin\Classes\BaseWidget
     */
    public function getListConfig($alias = null)
    {

        if (!$alias) {
            $alias = $this->primaryAlias;
        }

        if (!$listConfig = array_get($this->listConfig, $alias)) {
            $listConfig = $this->listConfig[$alias] = $this->makeConfig($this->listConfig[$alias], $this->requiredConfig);
        }

        return $listConfig;
    }

    // public function print($context, $recordId = null)
    // {
    //     $this->suppressLayout = TRUE;
    //     $data['model'] = $this->formFindModelObject($recordId);        
        
    //     $pdf = PDF::loadView('pdf_view', $data);  
    //     return $pdf->download('order' . $recordId . '.pdf');

        
    // }

    public function index_onLoadPopup() {
        $context = post('context');
        $orderId = (int)post('orderId');

        if (!in_array($context, ['orderPreview']))
             throw new ApplicationException('Invalid type specified - must be orderPreview');

        $oc = new OverviewController();

        return ['#previewModalContentGrouped' => $oc->previewModalContent($context, $orderId)];
         
    }

    public function print($context, $recordId = null)
    {
        $this->suppressLayout = TRUE;
        $data['model'] = $this->formFindModelObject($recordId);        
        
        return view('pdf_view', $data);
        // $pdf = PDF::loadView('pdf_view', $data);  
        // return $pdf->download('order' . $recordId . '.pdf');

        
    }

}