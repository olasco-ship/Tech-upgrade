<?php
namespace Tech\System\Cron;

use Magento\Framework\HTTP\Client\Curl;
use Magento\CatalogInventory\Api\StockRegistryInterface;


class TechIntegration
{
    
    /**
     * @var Curl
     */
    protected $curlClient;

    /**
     * @var string
     */
    protected $urlPrefix = 'https://';

    /**
     * @var string
     */
    protected $apiUrl = 'td-sat.sandbox.operations.dynamics.com/api/services/DynMagnetoServiceGroup/DynMagnetoItemOnHandList/getItemListV2';

    /**
     * @var string
     */
      protected $apiUri ='login.microsoftonline.com/473a0bc4-3b24-41dd-8637-31d1d34ae468/oauth2/token';

    	
     /**
     * @var StockRegistryInterface
     */
     protected $stockRegistry;


    /**
     * @param Curl $curl
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(Curl $curl, StockRegistryInterface $stockRegistry)
    {
        $this->curlClient = $curl;
        $this->stockRegistry = $stockRegistry;
         //parent::__construct();   
     }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->urlPrefix . $this->apiUrl;
    }

	public function getApiUrls() {
	
	return $this->urlPrefix . $this->apiUri;
        }

    /**
     * Gets productInfo json
     *
     * @return array
     */
    public function execute()
    {
        $userData = "grant_type=client_credentials&client_id=6358f49a-16c8-4aed-bc40-2df9dbf66e7f&client_secret=p-o9lBk~kAt7Mk5-noT4..c1D-B0539bhP&resource=https://td-sat.sandbox.operations.dynamics.com";
        $apiUri = $this->getApiUrls();
        $this->getCurlClient()->addHeader("Content-Type", "application/x-www-form-urlencoded");
        $this->getCurlClient()->post($apiUri, $userData);
        $trent = json_decode($this->getCurlClient()->getBody());
        $tren =  $trent->access_token;

 	//var_dump($tren);
         
        
         $body = array("_request" =>
         array("ItemId" => "",
            "Warehouse" => ""
         )
       );

         $postData = json_encode($body);

        $apiUrl = $this->getApiUrl();

            $this->getCurlClient()->addHeader("Content-Type", "application/json");
            $this->getCurlClient()->addHeader("Authorization", "Bearer $tren");
            $this->getCurlClient()->post($apiUrl, $postData);
            $response = json_decode($this->getCurlClient()->getBody());
	

	//$ola = json_decode($response);
   	//var_dump($response);
          //  die();
            
	//var_dump($response->ItemList['ItemId']);
	//die();

		
            //$te = json_encode(($response->ItemList));
           //$te = json_decode($response->ItemList);
                        

             
            //$adex = (json_decode($te));
            //var_dump($adex->ItemId);
	//die();
            $adex = json_decode(json_encode($response->ItemList));
            
	//var dump($adex);
	//die();

	 foreach ($adex as $item) {

           //var_dump($item->ItemId);
	//die();

	
                    $stockItem = $this->stockRegistry->getStockItemBySku($item->ItemId);       
       	            
                    $stockItem->setQty($item->Qty);

                    $stockItem->setProcessIndexEvents(false);
	       
                  $stockItem->setIsInStock((bool)$item->Qty);                   
                  $this->stockRegistry->updateStockItemBySku($sku, $stockItem);		
		
                    
                    }

            
                
       
    }

    /**
     * @return Curl
     */
    public function getCurlClient()
    {
        return $this->curlClient;
    }
}