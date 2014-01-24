<?php
class SM_Wishlist_Helper_Data extends Mage_Wishlist_Helper_Data
{
	public function getMoveAllFromCartUrl($arr_id)
	{
		return $this->_getUrl('wishlist/index/allfromcart',  $arr_id);
	}
}