<?php
namespace Tech\System\Cron;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\Client\Curl;


class CustomerCreate
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
    protected $apiUrl = 'td-sat.sandbox.operations.dynamics.com/data/CustomersV3';


    /**
     * @var string
     */
      protected $apiUri ='login.microsoftonline.com/473a0bc4-3b24-41dd-8637-31d1d34ae468/oauth2/token';

   /**
    *@var \Magento\Store\Model\StoreManagerInterface
    */
    protected $store;

    /**
     *@var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */

    protected $customerFactory;


    /**
    *@var \Magento\Customer\Api\CustomerRepositoryInterface
    */

    protected $customerRepository;
    
    /**
     * @param Curl $curl
     * @param StoreManagerInterface $store
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(Curl $curl, StoreManagerInterface $store, CustomerInterfaceFactory $customerFactory, CustomerRepositoryInterface $customerRepository)
    {
        $this->curlClient = $curl;
        $this->store = $store;
        $this->customerFactory =$customerFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {

         $number = 3;
         //.'?' .'$top=' . $number
        return $this->urlPrefix . $this->apiUrl .'?'. '$top=' . $number;
    }

	public function getApiUrls() {

       
	
	return $this->urlPrefix . $this->apiUri;
        }

    
    public function execute()
    {
        $userData = "grant_type=client_credentials&client_id=6358f49a-16c8-4aed-bc40-2df9dbf66e7f&client_secret=p-o9lBk~kAt7Mk5-noT4..c1D-B0539bhP&resource=https://td-sat.sandbox.operations.dynamics.com";
        $apiUri = $this->getApiUrls();
        $this->getCurlClient()->addHeader("Content-Type", "application/x-www-form-urlencoded");
        $this->getCurlClient()->post($apiUri, $userData);
        $trent = json_decode($this->getCurlClient()->getBody());
        $tren =  $trent->access_token;

 	
        $apiUrl = $this->getApiUrl();

            $this->getCurlClient()->addHeader("Content-Type", "application/json");
            $this->getCurlClient()->addHeader("Authorization", "Bearer $tren");
            $this->getCurlClient()->get($apiUrl);
            $response = json_decode($this->getCurlClient()->getBody());
	

	//$ola = json_decode($response);
   //var_dump($response);
           //die();

         $ola = json_encode($response->{'value'});
            
	$adex = json_decode($ola); 
//var_dump($response);
           //die();
           
	


	 foreach ($adex as $item) {

          //var_dump($item->PrimaryContactEmail);
	//die();
	
		
       
           /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
           $customer = $this->customerFactory->create();
           $customer->setStoreId($this->store->getStore()->getId());
           $customer->setWebsiteId($this->store->getStore()->getWebsiteId());
           $customer->setEmail($item->PrimaryContactEmail);
           $customer->setFirstname($item->OrganizationName);
           $customer->setLastname($item->OrganizationName);


           /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository*/
           $customer = $this->customerRepository->save($customer);

		            if($customer->getId()){

			continue;	
				}	
		
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