<?php

require_once 'Mage/Wishlist/controllers/IndexController.php';

class SM_Wishlist_IndexController extends Mage_Wishlist_IndexController
{

	public function allfromcartAction()
	{
		$wishlist = $this->_getWishlist();
		$arr_id = $this->getRequest()->getParams();
		foreach($arr_id as $itemId)
		{
			/* @var Mage_Checkout_Model_Cart $cart */
			$cart = Mage::getSingleton('checkout/cart');
			$session = Mage::getSingleton('checkout/session');

			try {
				$item = $cart->getQuote()->getItemById($itemId);
				if (!$item) {
					Mage::throwException(
						Mage::helper('wishlist')->__("Requested cart item doesn't exist")
					);
				}

				$productId  = $item->getProductId();
				$buyRequest = $item->getBuyRequest();

				$wishlist->addNewItem($productId, $buyRequest);

				$productIds[] = $productId;
				$cart->getQuote()->removeItem($itemId);
				$cart->save();
				Mage::helper('wishlist')->calculate();
				$productName = Mage::helper('core')->escapeHtml($item->getProduct()->getName());
				$wishlistName = Mage::helper('core')->escapeHtml($wishlist->getName());
				$session->addSuccess(
					Mage::helper('wishlist')->__("%s has been moved to wishlist %s", $productName, $wishlistName)
				);
				$wishlist->save();
			} catch (Mage_Core_Exception $e) {
				$session->addError($e->getMessage());
			} catch (Exception $e) {
				$session->addException($e, Mage::helper('wishlist')->__('Cannot move item to wishlist'));
			}

		}
		return $this->_redirectUrl(Mage::helper('checkout/cart')->getCartUrl());
	}

	public function updateAction()
	{
		if (!$this->_validateFormKey()) {
			return $this->_redirect('*/*/');
		}
		$wishlist = $this->_getWishlist();
		if (!$wishlist) {
			return $this->norouteAction();
		}

		$post = $this->getRequest()->getPost();
		if ($post && isset($post['description']) && is_array($post['description'])) {
			$updatedItems = 0;

			foreach ($post['description'] as $itemId => $description) {
				$item = Mage::getModel('wishlist/item')->load($itemId);
				if ($item->getWishlistId() != $wishlist->getId()) {
					continue;
				}

				// Extract new values
				$description = (string)$description;

				if ($description == Mage::helper('wishlist')->defaultCommentString()) {
					$description = '';
				} elseif (!strlen($description)) {
					$description = $item->getDescription();
				}

				$qty = null;
				if (isset($post['qty'][$itemId])) {
					$qty = $this->_processLocalizedQty($post['qty'][$itemId]);
				}
				if (is_null($qty)) {
					$qty = $item->getQty();
					if (!$qty) {
						$qty = 1;
					}
				} elseif (0 == $qty) {
					try {
						$item->delete();
					} catch (Exception $e) {
						Mage::logException($e);
						Mage::getSingleton('customer/session')->addError(
							$this->__('Can\'t delete item from wishlist')
						);
					}
				}

				// Check that we need to save
				if (($item->getDescription() == $description) && ($item->getQty() == $qty)) {
					continue;
				}
				try {
					$item->setDescription($description)
						->setQty($qty)
						->save();
					$updatedItems++;
				} catch (Exception $e) {
					Mage::getSingleton('customer/session')->addError(
						$this->__('Can\'t save description %s', Mage::helper('core')->escapeHtml($description))
					);
				}
			}

			// save wishlist model for setting date of last update
			if ($updatedItems) {
				try {
					$wishlist->save();
					Mage::helper('wishlist')->calculate();
				} catch (Exception $e) {
					Mage::getSingleton('customer/session')->addError($this->__('Can\'t update wishlist'));
				}
			}

			if (isset($post['save_and_share'])) {
				$this->_redirect('*/*/share', array('wishlist_id' => $wishlist->getId()));
				return;
			}
		}
		//$this->_redirect('*', array('wishlist_id' => $wishlist->getId()));
		$this->_redirectreferer('*/*/');
	}
}