<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 13/11/18
 * Time: 5:41 PM
 */

namespace Ced\Rewardsystem\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CancelOrder implements ObserverInterface
{
    public $serialize;
    public $pointCollectionFactory;

    public function __construct(
        \Ced\Rewardsystem\Model\ResourceModel\Regisuserpoint\CollectionFactory $pointCollectionFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize
    )
    {
        $this->serialize = $serialize;
        $this->pointCollectionFactory = $pointCollectionFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        // TODO: Implement execute() method.
        try{
            $order = $observer->getEvent()->getOrder();
            $id = $order->getId();
            $model = $this->pointCollectionFactory->create()->addFieldToFilter('order_id', $id)->getFirstItem();

            if( $model->getId() ){
                $model->setStatus( $order->getStatus() );
                $model->setUpdatedAt( $order->getUpdatedAt() );
                $model->save();
                return $this;

                /*
                 * no redemption process as the display manages non cancelled and cancelled orders
                 * redemption process = set redeemed point in order to handle partial approval or cancellation
                 * if expired points was used and expiration date has passed then no redemption
                //get item details for points
                $item_details = $this->serialize->unserialize($model->getItemDetails());
                $item_details = is_array($item_details) && !empty($item_details) ? array_column( $item_details, 'point', 'id') : [];

                foreach( $order->getAllItems() as $key => $item ) {
                    if ( !empty($item->getData()) )
                        $order_items[] = $item->getData();
                }

                //get count of cancelled items
                $cancelled_items = array_sum( array_column($order_items, 'qty_canceled') );
                //get total qty
                $total_qty_ordered = $order->getData('total_qty_ordered');

                if( $cancelled_items == $total_qty_ordered ){
                    $price_status = 'cancelled';
                     $model->setReceivedPoint( $model->getPointUsed );
                } */

                /* // required when partial shipment and partial cancellation is allowed
                if( !$cancelled_items ){
                    foreach( $order_items as $key => $item ) {
                        if( !empty($item->getData()) && $item->getData('qty_shipped') == $item->getData('qty_ordered') && array_key_exists( $item->getData('product_id'), $item_details )){
                            $point_val += $item_details[$item->getData('product_id')];
                        }
                    }
                }*/
            }
        } catch (\Exception $e){
            throw new \Magento\Framework\Exception\LocalizedException ( __($e->getMessage()) );
        }
    }
}