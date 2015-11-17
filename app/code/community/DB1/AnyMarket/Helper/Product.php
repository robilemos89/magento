<?php

class DB1_AnyMarket_Helper_Product extends DB1_AnyMarket_Helper_Data
{

    public function getConfigs($IDStore){
        $ConfigsReturn = array(
            "REQUIRED_NBM" => Mage::getStoreConfig('anymarket_section/anymarket_general_group/anymarket_NBM_required_field', $IDStore),
            "REQUIRED_EAN" => Mage::getStoreConfig('anymarket_section/anymarket_general_group/anymarket_EAN_required_field', $IDStore),

            "brand" => Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_brand_field', $IDStore),
            "model" => Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_model_field', $IDStore),

            "volume_comprimento" => Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_vol_comp_field', $IDStore),
            "volume_altura" => Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_vol_alt_field', $IDStore),
            "volume_largura" => Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_vol_larg_field', $IDStore),
            "video_url" => Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_video_url_field', $IDStore),
            "nbm" => Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_nbm_field', $IDStore),
            "nbm_origin" => Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_nbm_origin_field', $IDStore),
            "ean" => Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_ean_field', $IDStore),
            "warranty_text" => Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_warranty_text_field', $IDStore),
            "warranty_time" => Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_warranty_time_field', $IDStore)
        );

        return $ConfigsReturn;
    }

    public function getFullDescription($product){
        $ConfigDescProd = Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_desc_field', Mage::app()->getStore()->getId());
        $descComplete = "";
        if ($ConfigDescProd && $ConfigDescProd != 'a:0:{}') {
            $ConfigDescProd = unserialize($ConfigDescProd);
            if (is_array($ConfigDescProd)) {
                foreach($ConfigDescProd as $ConfigDescProdRow) {
                    $descComplete .= $product->getData( $ConfigDescProdRow['descProduct'] ).' ';
                }
            }
        }else{
            $descComplete = $product->getDescription();
        }

        return trim($descComplete);
    }

	//GERA LOG DOS PRODUTOS
    public function saveLogsProds($returnProd, $product){
        $anymarketproductsUpdt = Mage::getModel('db1_anymarket/anymarketproducts')->load($product->getId(), 'nmp_id');        
        $returnMet = "";

        if($returnProd['error'] == '1'){ //RETORNOU ERRO
            $JSONError = $returnProd['return'];
            if($anymarketproductsUpdt->getData('nmp_sku') == null){
                $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts');
                $anymarketproducts->setNmpId( $product->getId() );
                $anymarketproducts->setNmpSku( $product->getSku() );
                $anymarketproducts->setNmpName( $product->getName() );
                $anymarketproducts->setNmpDescError( $returnProd['return'] );
                $anymarketproducts->setNmpStatusInt("Erro");
                $anymarketproducts->setStatus("1");
                $anymarketproducts->save();
            }else{
                $anymarketproductsUpdt->setNmpId( $product->getId() );
                $anymarketproductsUpdt->setNmpSku( $product->getSku() );
                $anymarketproductsUpdt->setNmpName( $product->getName() );
                $anymarketproductsUpdt->setNmpDescError( $returnProd['return'] );
                $anymarketproductsUpdt->setNmpStatusInt("Erro");
                $anymarketproductsUpdt->setStatus("1");
                $anymarketproductsUpdt->save();
            }

            $URL = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id' => $product->getId() ));
            $this->addMessageInBox('Erro ao sincronizar produtos AnyMarket', $returnProd['return'], $URL);
            $returnMet = $returnProd['return'];
        }else{ //FOI BEM SUCEDIDO
            if($anymarketproductsUpdt->getData('nmp_sku') == null){
                $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts');
                $anymarketproducts->setNmpId( $product->getId() );
                $anymarketproducts->setNmpSku( $product->getSku() );
                $anymarketproducts->setNmpName( $product->getName() );
                $anymarketproducts->setNmpDescError("");
                $anymarketproducts->setNmpStatusInt("Integrado");
                $anymarketproducts->setStatus("1");
                $anymarketproducts->save();
            }else{
                $anymarketproductsUpdt->setNmpId( $product->getId() );
                $anymarketproductsUpdt->setNmpSku( $product->getSku() );
                $anymarketproductsUpdt->setNmpName( $product->getName() );
                $anymarketproductsUpdt->setNmpDescError("");
                $anymarketproductsUpdt->setNmpStatusInt("Integrado");
                $anymarketproductsUpdt->setStatus("1");
                $anymarketproductsUpdt->save();
            }

            $returnMet = $returnProd['return'];
        }

        $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
        $anymarketlog->setLogDesc( $returnMet );
        $anymarketlog->setLogId( $product->getSku() );
        $anymarketlog->setLogJson( $returnProd['json'] );
        $anymarketlog->setStatus("1");
        $anymarketlog->save();
    }

    private function compareArrayImage($var_1, $var_2){
        $arrayReturn = array();
        foreach ($var_1 as $value_1) {
            $hSamVal = false;
            foreach ($var_2 as $value_2) {
                $ArrtratValue1 = explode("_", $value_1['ctrl']);
                $ArrtratValue2 = explode("_", $value_2['ctrl']);                

                $tratValue1 = array_shift(array_values($ArrtratValue1));
                $tratValue2 = array_shift(array_values($ArrtratValue2));
                if( $tratValue1 == $tratValue2 ){
                    $hSamVal = true;
                    break;
                }
            }
            if(!$hSamVal){
                $arrayReturn[] = $value_1;
            }
        }
        return $arrayReturn;
    }

    //CRIA PRODUTO CONFIG MG - TODO
    private function create_configurable_product($dataProdConfig, $simpleProducts, $AttributeIds){
        $productGenerator = Mage::helper('db1_anymarket/productgenerator');
        $product = $productGenerator->createConfigurableProduct($dataProdConfig, $simpleProducts, $AttributeIds);
        
        $returnProd['return'] = Mage::helper('db1_anymarket')->__('Configurable product Created');
        $returnProd['error'] = '0';
        $returnProd['json'] = '';
        $this->saveLogsProds($returnProd, $product);

        return $product;
    }

    //UPDATE PRODUTO CONFIG MG - TODO
    private function update_configurable_product($idProd, $dataProdConfig, $simpleProducts, $AttributeIds){
        $productGenerator = Mage::helper('db1_anymarket/productgenerator');
        $product = $productGenerator->updateConfigurableProduct($idProd, $dataProdConfig, $simpleProducts, $AttributeIds);
        
        $returnProd['return'] = Mage::helper('db1_anymarket')->__('Configurable product Updated');
        $returnProd['error'] = '0';
        $returnProd['json'] = '';
        $this->saveLogsProds($returnProd, $product);

        return $product;
    }

    //CRIA PRODUTO SIMPLES MG
    function create_simple_product($data){
        $productGenerator = Mage::helper('db1_anymarket/productgenerator');
        $product = $productGenerator->createSimpleProduct($data);
        
        if(!$product){
            $returnProd['return'] = Mage::helper('db1_anymarket')->__('Simple product Created');
            $returnProd['error'] = '0';
            $returnProd['json'] = '';
            $this->saveLogsProds($returnProd, $product);
        }

        return $product;
    }

    public function procAttrConfig($attrCode, $attrVal, $typeProc){
        $_product = Mage::getModel('catalog/product');
        $attr = $_product->getResource()->getAttribute($attrCode);

        if ($attr->usesSource()) {
            if($typeProc == 0){
                return $attr->getSource()->getOptionId((string)$attrVal);
            }else{
                return $attr->getSource()->getOptionText($attrVal);
            }
        }else{
            return $attrVal;
        }

    }

    //ATUALIZA AS IMAGENS DE UM PROD
    public function update_image_product($Prod, $ProdsJSON, $idClient){
        $mediaApi = Mage::getModel("catalog/product_attribute_media_api");
        $items = $mediaApi->items($Prod->getId());
        $imagesGalleryMG = array();
        foreach($items as $item) {
            $crltImg = basename($item['file']);
            $crltImg = str_replace(strrchr($crltImg,"."), "", $crltImg);
            $imagesGalleryMG[] = array('ctrl' => $crltImg, 'img' => $item['url'], 'file' => $item['file'] );
        }

        $imagesGalleryAM = array();
        foreach ($ProdsJSON->photos as $image) {
            $crltImgAM = basename($image->original);
            $crltImgAM = str_replace(strrchr($crltImgAM,"."), "", $crltImgAM);
            $imagesGalleryAM[] = array('ctrl' => md5($crltImgAM . $idClient), 'img' => $image->standard_resolution, 'main' => $image->main);
        }

        //COMPARA IMG AM COM MG SE TIVER DIVERGENCIA ADD NO PRODUTO
        $diffAM = $this->compareArrayImage($imagesGalleryAM, $imagesGalleryMG);
        if ($diffAM) {
            foreach ($diffAM as $diffAM_value) {
                $imagesGallery[] = array('img' => $diffAM_value['img'], 'main' => $diffAM_value['main']);
            }

            $dataImgs = array('images' => $imagesGallery, 'sku' => $idClient);
            $productGenerator = Mage::helper('db1_anymarket/productgenerator');
            $productGenerator->updateImages($Prod, $dataImgs);
        }

        //COMPARA IMG AM COM MG SE TIVER DIVERCIA REMOVE DO PRODUTO
        $diffMG = $this->compareArrayImage($imagesGalleryMG, $imagesGalleryAM);
        if ($diffMG) {
            foreach ($diffMG as $diffMG_value) {
                $mediaApi->remove($Prod->getId(), $diffMG_value['file']);
                //remover arquivo fisicamente
            }
        }
    }

	//ENVIA PRODUTO PARA O ANYMARKET
    public function sendProductToAnyMarket($idProduct){
        //obter configuracoes - Util MultLoja
        $product =  Mage::getModel('catalog/product')->load($idProduct);
        //$storeIds = $_product->getStoreIds();
        //foreach ($storeIds as $store) {
            $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', Mage::app()->getStore()->getId());
            $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', Mage::app()->getStore()->getId());

            $REQUIRED_NBM = Mage::getStoreConfig('anymarket_section/anymarket_general_group/anymarket_NBM_required_field', Mage::app()->getStore()->getId());
            $REQUIRED_EAN = Mage::getStoreConfig('anymarket_section/anymarket_general_group/anymarket_EAN_required_field', Mage::app()->getStore()->getId());

            $MassUnit = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_weight_field', Mage::app()->getStore()->getId());

            //Obtem os parametros dos attr para subir para o AM
            $brand  =             Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_brand_field', Mage::app()->getStore()->getId());
            $model =              Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_model_field', Mage::app()->getStore()->getId()); 

            $volume_comprimento = Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_vol_comp_field', Mage::app()->getStore()->getId()); 
            $volume_altura =      Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_vol_alt_field', Mage::app()->getStore()->getId()); 
            $volume_largura =     Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_vol_larg_field', Mage::app()->getStore()->getId()); 
            $video_url =          Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_video_url_field', Mage::app()->getStore()->getId()); 
            $nbm =                Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_nbm_field', Mage::app()->getStore()->getId()); 
            $nbm_origin =         Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_nbm_origin_field', Mage::app()->getStore()->getId()); 
            $ean =                Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_ean_field', Mage::app()->getStore()->getId()); 
            $warranty_text =      Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_warranty_text_field', Mage::app()->getStore()->getId()); 
            $warranty_time =      Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_warranty_time_field', Mage::app()->getStore()->getId()); 

            //verifica se o produto e configurable
            $confID = "";
            $Weight = "";
            if($product->getTypeID() == "configurable"){
                $confID = $product->getId();
            }else{
                // verifica se é um simples pertecente a um Configurable
                $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild( $product->getId() );
                $Weight = $product->getWeight();
                if (isset($parentIds[0])) {
                    $confID = $parentIds[0];
                    $product = Mage::getModel('catalog/product')->load($confID);
                }
            }

            //obtem os produtos configs - verifica se e configurable
            $ArrSimpleConfigProd = array();
            $ArrayVariations = array();
            if($confID != ""){
                $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
                $attributesConf = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product); 

                //obter as variacoes
                $attrs = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
                foreach($attrs as $attr) {
                    $ArrayVariations[] = array(
                                               "id" => null,
                                               "name" => $attr['label']
                                              );
                }

                foreach($childProducts as $child) {
                    $SimpleConfigProd = Mage::getModel('catalog/product')->load( $child->getId() ); 

                    if($Weight == ""){
                        $Weight = $SimpleConfigProd->getWeight();
                    }

                    //obtem as imagens do produto
                    $galleryDataSimp = $SimpleConfigProd->getMediaGalleryImages();
                    $itemsIMGSimp = array();
                    foreach($galleryDataSimp as $g_imageSimp) {
                        $itemsIMGSimp[] = $g_imageSimp['url'];
                    }

                    //obtem os atributos
                    $ArrVariationValues = array();
                    foreach ($attributesConf as $attribute){
                        foreach ($attribute['values'] as $value){
                            $childValue = $child->getData($attribute['attribute_code']);
                            if ($value['value_index'] == $childValue){
                                $ArrVariationValues[$attribute['store_label']] = $value['store_label'];
                            }
                        }
                    }

                    $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $storeIds));
                    $stkPrice = $SimpleConfigProd->getData($filter);
                    $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($SimpleConfigProd);
                    $ArrSimpleConfigProd[] = array(
                        "urlImages" => $itemsIMGSimp,
                        "variationValues" => $ArrVariationValues,//$ArrVariationValues,
                        "stockPrice" => $stkPrice,
                        "stockAmount" => $stock->getQty(),
                        "ean" => $SimpleConfigProd->getData($ean),
                        "id" => null,
                        "title" => $SimpleConfigProd->getName(),
                        "idProduct" => null,
                        "internalId" => $SimpleConfigProd->getSku(),
                    );

                }

            }

            //obtem as imagens do produto
            $itemsIMG = array();
            $galleryData = $product->getMediaGalleryImages();
            foreach($galleryData as $g_image) {
                $itemsIMG[] = $g_image['url'];
            }

            //ajusta o array de skus
            if( count($ArrSimpleConfigProd) <= 0 ){
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

                $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', Mage::app()->getStore()->getId()));
                $stkPrice = $product->getData($filter);
                $ArrSimpleConfigProd[] = array(
                    "urlImages" => null,
                    "variationValues" => null,
                    "stockPrice" => $stkPrice,
                    "stockAmount" => $stock->getQty(),
                    "ean" => $product->getData( $ean ), //esse
                    "id" => null,
                    "title" => $product->getName(),
                    "idProduct" => null,
                    "internalId" => $product->getSku(),
                );

                $ArrayVariations = null;
            }

            $Requiredean = $REQUIRED_EAN != '1' ? 'false' : 'true';
            $Requirednbm = $REQUIRED_NBM != '1' ? 'false' : 'true';        
            
            //cria os headers
            $headers = array( 
                "Content-type: application/json", 
                "Cache-Control: no-cache",
                "create_brand: true",
                "crop_title: false",
                "ignore_invalid_ean: ".$Requiredean,
                "create_category: true",
                "require_nbm: ".$Requirednbm,
                "gumgaToken: ".$TOKEN
            );

            $idProductAnyMarket = null;
            if($product->getData('id_anymarket') != ""){
                $idProductAnyMarket = $product->getData('id_anymarket');
            }


            //cria os custom attributes
            $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
            $attributeSetModel->load($product->getAttributeSetId());
            $attributeSetName  = $attributeSetModel->getAttributeSetId();

            $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->setAttributeSetFilter($attributeSetName)
            ->getItems();

            $ArrAttributes = array();
            $contIndexAttr = 0;
            foreach ($attributes as $attribute){
                $attrCheck =  Mage::getModel('db1_anymarket/anymarketattributes')->load($attribute->getAttributeId(), 'nma_id_attr');
                if($attrCheck->getData('nma_id_attr') != null){
                    if($attrCheck->getData('status') == 1){
                        if( ($attribute->getAttributeCode() != $brand) && ($attribute->getAttributeCode() != $model) ){
                            
                            if($confID == ""){
                                $ArrAttributes[] = array("index" => $contIndexAttr, "name" => $attribute->getFrontendLabel(), "value" => $this->procAttrConfig($attribute->getAttributeCode(), $product->getData( $attribute->getAttributeCode() ), 1));
                                $contIndexAttr = $contIndexAttr+1;
                            }else{
                                foreach ($attributesConf as $attributeConf){
                                    if(!in_array($attribute->getAttributeCode(), $attributeConf)){
                                        $ArrAttributes[] = array("index" => $contIndexAttr, "name" => $attribute->getFrontendLabel(), "value" => $this->procAttrConfig($attribute->getAttributeCode(), $product->getData( $attribute->getAttributeCode() ), 1));
                                        $contIndexAttr = $contIndexAttr+1;
                                    }
                                }
                            }
                        }
                    }
                }

            }

            //busca a categoria
            $anymarketCat = Mage::getModel('db1_anymarket/anymarketcategories')->load($product->getData('categoria_anymarket'), 'nmc_cat_id');
            $catString = $anymarketCat->getData('nmc_cat_desc');

            //Cria os params
            $param = array(
                'id' => $idProductAnyMarket,
                'title' => $product->getName(),
                'description' => $this->getFullDescription($product),//$product->getDescription(),
                'nbm' =>  $this->procAttrConfig($nbm, $product->getData( $nbm ), 1),
                'nbmOrigin' => $this->procAttrConfig($nbm_origin, $product->getData( $nbm_origin ), 1),
                "category" => array(
                    "id" => $product->getData('categoria_anymarket'),
                    "name" => $catString
                ),
                "brand" => array(
                    "id" => null,
                    "name" => $this->procAttrConfig($brand, $product->getData( $brand ), 1)
                ),
                "model" => array(
                    "id" => null,
                    "name" => $this->procAttrConfig($model, $product->getData( $model ), 1)
                ),
                "videoURL" => $this->procAttrConfig($video_url, $product->getData( $video_url ), 1),
                "warrantyText" => $this->procAttrConfig($warranty_text, $product->getData( $warranty_text ), 1),
                "warrantyTime" => $this->procAttrConfig($warranty_time, $product->getData( $warranty_time ), 1),
                "weight" => $MassUnit == 0 ? $Weight/1 : $Weight/1000,
                "height" => $this->procAttrConfig($volume_altura, $product->getData( $volume_altura ), 1),
                "width" => $this->procAttrConfig($volume_largura, $product->getData( $volume_largura ), 1),
                "length" => $this->procAttrConfig($volume_comprimento, $product->getData( $volume_comprimento ), 1),
                "urlImages" => $itemsIMG,
                // OBTER ATRIBUTOS CUSTOM
                "attributes" => $ArrAttributes,
                "skus" => $ArrSimpleConfigProd,
                "variations" => $ArrayVariations
            );

            if( ($product->getData('id_anymarket') == '') || ($product->getData('id_anymarket') == '0') ){
                $returnProd = $this->CallAPICurl("POST", $HOST."/rest/api/v1/products/", $headers, $param);
                $IDinAnymarket = '0';
                if($returnProd['error'] != '1'){
                    $SaveLog = $returnProd['return'];
                    $IDinAnymarket = json_encode($SaveLog->id);

                    if($IDinAnymarket != '0'){
                        $product->setIdAnymarket($IDinAnymarket);
                    }
                    $product->save();

                    if($product->getTypeID() == "configurable"){
                        $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);

                        foreach($childProducts as $child) {
                            $productC = Mage::getModel('catalog/product')->load( $child->getId() );

                            if($productC->getIntegraAnymarket() != '1'){
                                $productC->setIntegraAnymarket('1');
                            }
                            if($IDinAnymarket != '0'){
                                $productC->setIdAnymarket($IDinAnymarket);
                            }
                            $productC->save();
                        }
                    }

                }
                if($IDinAnymarket != '0'){
                    $this->saveLogsProds($returnProd, $product);
                }else{
                    $returnProd['error'] = '1';
                    $returnProd['return'] = Mage::helper('db1_anymarket')->__('Error synchronizing, code anymarket invalid.');
                    $this->saveLogsProds($returnProd, $product);
                }
            }else{
                $returnProd = $this->CallAPICurl("PUT", $HOST."/rest/api/v1/products/".$product->getData('id_anymarket'), $headers, $param);

                if($returnProd['error'] == '0'){
                    $returnProd['return'] = Mage::helper('db1_anymarket')->__('Product Updated');
                }
                
                $this->saveLogsProds($returnProd, $product);
            }
        //}

    }

    //OBTEM APENAS OS PRODUTOS QUE SOFRERAM MODIFICACOES
    public function getFeedProdsFromAnyMarket(){
        $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', Mage::app()->getStore()->getId());
        $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', Mage::app()->getStore()->getId());

        $headers = array( 
            "Content-type: application/json",
            "Accept: */*",
            "gumgaToken: ".$TOKEN
        );

        $returnProd = $this->CallAPICurl("GET", "http://sandbox-api.anymarket.com.br/v2/products/feeds/", $headers, null);

        if($returnProd['error'] == '1'){
            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc( Mage::helper('db1_anymarket')->__('Error on get feed products '). $returnProd['return'] );
            $anymarketlog->setStatus("1");
            $anymarketlog->save();
        }else{
            $listProds = $returnProd['return'];
            foreach ($listProds as  $product) {
                $this->getSpecificProductAnyMarket($product->id);
            }
        }

    }

    //OBTEM OS PRODUTOS DO AM
    public function getProdsFromAnyMarket(){
        $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', Mage::app()->getStore()->getId());
        $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', Mage::app()->getStore()->getId());

        $headers = array( 
            "Content-type: application/json",
            "Accept: */*",
            "gumgaToken: ".$TOKEN
        );

        $startRec = 0;
        $countRec = 1;
        $arrOrderCod = null;

        Mage::getSingleton('core/session')->setImportProdsVariable('false');
        $cont = 0;
        while ($startRec <= $countRec) {
            $returnProd = $this->CallAPICurl("GET", $HOST."/rest/api/v1/products/?start=".$startRec."&pageSize=30", $headers, null);

            if($returnProd['error'] == '1'){
                $startRec = 1;
                $countRec = 0;

                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                $anymarketlog->setLogDesc( Mage::helper('db1_anymarket')->__('Error on import products from anymarket '). $returnProd['return'] );
                $anymarketlog->setStatus("1");
                $anymarketlog->save();
            }else{
                $ProdsJSON = $returnProd['return'];

                $startRec = $ProdsJSON->pageable->start + $ProdsJSON->pageable->pageSize;
                $countRec = $ProdsJSON->total;

                foreach ($ProdsJSON->content as $product) {
                    $this->getSpecificProductAnyMarket($product->id);

                    $cont = $cont+1;
                }
            }
        }
        Mage::getSingleton('core/session')->setImportProdsVariable('true');

        return $cont;
    }

    //OBTEM UM ITEM ESPECIFICO DO AM
    public function getSpecificProductAnyMarket($IDProd){
        $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', Mage::app()->getStore()->getId());
        $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', Mage::app()->getStore()->getId());

        $headers = array( 
            "Content-type: application/json",
            "Accept: */*",
            "gumgaToken: ".$TOKEN
        );

        $productCreated = null;
        $returnProdSpecific = $this->CallAPICurl("GET", $HOST."/rest/api/v1/products/".$IDProd, $headers, null);
        if($returnProdSpecific['error'] == '0'){
            $productCreated = $this->createProducts($returnProdSpecific['return']);
        }else{
            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc( $returnProdSpecific['return'] );
            $anymarketlog->setLogId( $IDProd ); 
            $anymarketlog->setStatus("1");
            $anymarketlog->save();
        }

        return $productCreated;
    }


    //CRIA PRODUTO NO MG
    public function createProducts($ProdsJSON){
        $ProdCrt = null;
        $priceField = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', Mage::app()->getStore()->getId()));

        $brand  =             Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_brand_field', Mage::app()->getStore()->getId());
        $model =              Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_model_field', Mage::app()->getStore()->getId()); 

        $MassUnit = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_weight_field', Mage::app()->getStore()->getId());

        $volume_comprimento = Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_vol_comp_field', Mage::app()->getStore()->getId()); 
        $volume_altura =      Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_vol_alt_field', Mage::app()->getStore()->getId()); 
        $volume_largura =     Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_vol_larg_field', Mage::app()->getStore()->getId()); 
        $video_url =          Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_video_url_field', Mage::app()->getStore()->getId()); 
        $nbm =                Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_nbm_field', Mage::app()->getStore()->getId()); 
        $nbm_origin =         Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_nbm_origin_field', Mage::app()->getStore()->getId()); 
        $ean =                Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_ean_field', Mage::app()->getStore()->getId()); 
        $warranty_text =      Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_warranty_text_field', Mage::app()->getStore()->getId()); 
        $warranty_time =      Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_warranty_time_field', Mage::app()->getStore()->getId()); 

        //PROD CONFIGURABLE
        if ( !empty( $ProdsJSON->variations ) ) {
            $prodSimpleFromConfig = array();
            $AttributeIds = array();
            $AttributeOptions = array();

            $variationArray = array();
            $sinc = '';
            foreach ($ProdsJSON->variations as $variation) {
                $variationArray[$variation->id] = strtolower($variation->name);
                $AttrCtlr = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', strtolower($variation->name));
                if(!$AttrCtlr->getData()){
                    $sinc = strtolower($variation->name);
                    break;
                }
            }

            if($sinc == ''){
                foreach ($ProdsJSON->skus as  $sku) {
                    $IDSKUProd = $sku->idInClient != null ? $sku->idInClient : $sku->idProduct;
                    $ProdCrt = '';
                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $IDSKUProd);

                    foreach ($sku->variations as  $varValues) {
                        $descVar = $varValues->description;
                        $idVar = $varValues->variationTypeId;
                    }

                    $imagesGallery = array();
                    foreach ($ProdsJSON->photos as $image) {
                        if( $image->variationValue != null ){
                            if( $image->variationValue->description == $descVarAttr ){
                                $imagesGallery[] = array('img' => $image->standard_resolution, 'main' => $image->main);
                                break;
                            }
                        }
                    }

                    if(!$product){
                        $AttributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_product', $variationArray[ $idVar ]);
                        if (!in_array($AttributeId, $AttributeIds)) {
                            $AttributeIds[] = $AttributeId;
                            $collectionAttr = Mage::getResourceModel('eav/entity_attribute_option_collection')
                                             ->setPositionOrder('asc')
                                             ->setAttributeFilter($AttributeId)
                                             ->setStoreFilter(0)
                                             ->load();

                            $AttributeOptions[$idVar] = $collectionAttr->toOptionArray();

                        }

                        $varAttr = '';
                        $descVarAttr = '';
                        foreach ( $AttributeOptions[$idVar] as $attrOpt) {
                            if($attrOpt['label'] == $descVar ){
                                $varAttr = $attrOpt['value'];
                                $descVarAttr = $attrOpt['label'];
                                break;
                            }
                        }

                        if($varAttr != ''){
                            $dataPrdSimple = array(
                                'product' => array(
                                    'type_id' =>  'simple',

                                    'sku' => $IDSKUProd,
                                    'name' => $sku->title,
                                    'description' => $sku->title,
                                    'short_description' => $sku->title,
                                    $priceField => $sku->price,
                                    'created_at' =>  strtotime('now'),
                                    'updated_at' =>  strtotime('now'),
                                    'id_anymarket' => $sku->idProduct,
                                    'weight' => $MassUnit == 1 ? $ProdsJSON->weight*1000 : $ProdsJSON->weight,

                                    $brand => $ProdsJSON->brand != null ? $this->procAttrConfig($brand, $ProdsJSON->brand->name, 0) : null,
                                    $model => $this->procAttrConfig($model, $ProdsJSON->model, 0),
                                    $video_url => $this->procAttrConfig($video_url, $ProdsJSON->videoURL, 0),
                                    $volume_comprimento => $this->procAttrConfig($volume_comprimento, $ProdsJSON->length, 0),
                                    $volume_altura => $this->procAttrConfig($volume_altura, $ProdsJSON->height, 0),
                                    $volume_largura => $this->procAttrConfig($volume_largura, $ProdsJSON->width, 0),
                                    $warranty_time => $this->procAttrConfig($warranty_time, $ProdsJSON->warrantyTime, 0),
                                    $nbm => $this->procAttrConfig($nbm, $ProdsJSON->nbm, 0),
                                    $nbm_origin => $this->procAttrConfig($nbm_origin, $ProdsJSON->originCode, 0),
                                    $ean => $this->procAttrConfig($ean, $sku->ean, 0),
                                    $warranty_text => $this->procAttrConfig($warranty_text, $ProdsJSON->warranty, 0),
                                    'msrp_enabled' =>  '2',
                                    'categoria_anymarket' => $ProdsJSON->category->id,
                                    $variationArray[ $idVar ] => $varAttr,
                                ),
                                'stock_item' => array(
                                    'is_in_stock' =>  $sku->stockAmount > 0 ? '1' : '0',
                                    'qty' => $sku->stockAmount,
                                ),
                                'images' => $imagesGallery,
                            );

                            $ProdReturn = $this->create_simple_product($dataPrdSimple);
                            $ProdCrt = $ProdReturn->getEntityId();

                        }else{
                            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                            $anymarketlog->setLogDesc( 'Opção de variação sem correspondente no magento ('.$variationArray[ $idVar ].') - '.$descVar );
                            $anymarketlog->setStatus("1");
                            $anymarketlog->save();
                        }
                    }else{
                        //Atualiza Imagens
                        $this->update_image_product($product, $ProdsJSON, $IDSKUProd);

                        $product->setDescription( $sku->title );
                        $product->setShortDescription( $sku->title );
                        $product->setData($priceField, $sku->price);

                        $product->setData($brand, $ProdsJSON->brand != null ? $this->procAttrConfig($brand, $ProdsJSON->brand->name, 0) : null);
                        $product->setData($model, $this->procAttrConfig($model, $ProdsJSON->model, 0));
                        $product->setData($video_url, $this->procAttrConfig($video_url, $ProdsJSON->videoURL, 0));
                        $product->setData($volume_comprimento, $this->procAttrConfig($volume_comprimento, $ProdsJSON->length, 0));
                        $product->setData($volume_altura, $this->procAttrConfig($volume_altura, $ProdsJSON->height, 0));
                        $product->setData($volume_largura, $this->procAttrConfig($volume_largura, $ProdsJSON->width, 0));
                        $product->setData($warranty_time, $this->procAttrConfig($warranty_time, $ProdsJSON->warrantyTime, 0));
                        $product->setData($nbm, $this->procAttrConfig($nbm, $ProdsJSON->nbm, 0));
                        $product->setData($nbm_origin, $this->procAttrConfig($nbm_origin, $ProdsJSON->originCode, 0));
                        $product->setData($ean, $this->procAttrConfig($ean, $sku->ean, 0));
                        $product->setData($warranty_text, $this->procAttrConfig($warranty_text, $ProdsJSON->warranty, 0));
                
                        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                        $stockItem->setData('is_in_stock',  $ProdsJSON->skus[0]->stockAmount > 0 ? '1' : '0');
                        $stockItem->setData('qty', $ProdsJSON->skus[0]->stockAmount);
                        $stockItem->save();


                        $product->save();

                        $ProdCrt = $product->getId();
                    }

                    if($ProdCrt != ''){
                        $prodSimpleFromConfig[] = array('AttributeText' => $variationArray[ $idVar ], 'Id' => $ProdCrt);
                        $ProdCrt = '';
                    }
                }

                $prods = Mage::getModel('catalog/product')->getCollection()
                                                          ->addFieldToFilter('type_id', 'configurable')
                                                          ->addFieldToFilter('id_anymarket', $ProdsJSON->id);

                $imagesGallery = array();
                foreach ($ProdsJSON->photos as $image) {
                    $imagesGallery[] = array('img' => $image->standard_resolution, 'main' => $image->main);
                }

                if( !$prods->getData() ){
                    if($prodSimpleFromConfig){
                        $dataProdConfig = array(
                            'stock' => '0',
                            'price' => '0',
                            'name' => $ProdsJSON->title,
                            'short' => $ProdsJSON->description,
                            'description' => $ProdsJSON->description,
                            'brand' => '',
                            'sku' => $ProdsJSON->id,
                            'id_anymarket' => $ProdsJSON->id,
                            'categoria_anymarket' => $ProdsJSON->category->id,
                            'images' => $imagesGallery
                        );

                        $ProdCrt = $this->create_configurable_product($dataProdConfig, $prodSimpleFromConfig, $AttributeIds);
                    }
                }else{
                    $dataProdConfig = array(
                        'stock' => '0',
                        'price' => '0',
                        'name' => $ProdsJSON->title,
                        'short' => $ProdsJSON->description,
                        'description' => $ProdsJSON->description,
                        'brand' => '',
                        'sku' => $ProdsJSON->id,
                        'id_anymarket' => $ProdsJSON->id,
                        'categoria_anymarket' => $ProdsJSON->category->id
                    );

                    foreach($prods as $prod) {
                        $IdProdConfig = $prod->getId();
                    }

                    $ProdCrt = $IdProdConfig;
                    $this->update_configurable_product($ProdCrt, $dataProdConfig, $prodSimpleFromConfig, $AttributeIds);
                }
            }else{
                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                $anymarketlog->setLogDesc( 'Variação sem correspondente no magento ('.utf8_decode($sinc).') - Produto: '.$ProdsJSON->id);
                $anymarketlog->setStatus("1");
                $anymarketlog->save();
            }

        }else{
            foreach ($ProdsJSON->skus as $ProdJSON) {
                $skuProd = $ProdJSON;
            }

            $imagesGallery = array();
            foreach ($ProdsJSON->photos as $image) {
                $imagesGallery[] = array('img' => $image->standard_resolution, 'main' => $image->main);
            }

            $IDSkuJsonProd = $skuProd->idInClient != null ? $skuProd->idInClient : $skuProd->idProduct;
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $IDSkuJsonProd);
            foreach ($ProdsJSON->skus as  $sku) {
                $skuEan = $sku->ean;
            }

            if(!$product){
                $data = array(
                    'product' => array(
                        'type_id' =>  'simple',

                        'sku' => $IDSkuJsonProd,
                        'name' => $skuProd->title,
                        'description' => $ProdsJSON->description,
                        'short_description' => $ProdsJSON->description,
                         $priceField => $skuProd->price,
                        'created_at' => strtotime('now'),
                        'updated_at' => strtotime('now'),
                        'id_anymarket' => $ProdsJSON->id,
                        'weight' => $MassUnit == 1 ? $ProdsJSON->weight*1000 : $ProdsJSON->weight,

                        $brand => $ProdsJSON->brand != null ? $this->procAttrConfig($brand, $ProdsJSON->brand->name, 0) : null,
                        $model => $this->procAttrConfig($model, $ProdsJSON->model, 0),
                        $video_url => $this->procAttrConfig($video_url, $ProdsJSON->videoURL, 0),
                        $volume_comprimento => $this->procAttrConfig($volume_comprimento, $ProdsJSON->length, 0),
                        $volume_altura => $this->procAttrConfig($volume_altura, $ProdsJSON->height, 0),
                        $volume_largura => $this->procAttrConfig($volume_largura, $ProdsJSON->width, 0),
                        $warranty_time => $this->procAttrConfig($warranty_time, $ProdsJSON->warrantyTime, 0),
                        $nbm => $this->procAttrConfig($nbm, $ProdsJSON->nbm, 0),
                        $nbm_origin => $this->procAttrConfig($nbm_origin, $ProdsJSON->originCode, 0),
                        $ean => $this->procAttrConfig($ean, $skuEan, 0),
                        $warranty_text => $this->procAttrConfig($warranty_text, $ProdsJSON->warranty, 0),
                        'msrp_enabled' =>  '2',
                        'categoria_anymarket' => $ProdsJSON->category->id,
                    ),
                    'stock_item' => array(
                        'is_in_stock' =>  $skuProd->stockAmount > 0 ? '1' : '0',
                        'qty' => $skuProd->stockAmount,
                    ),
                    'images' => $imagesGallery,

                );

                $ProdCrt = $this->create_simple_product($data);
            }else{
                //Atualiza Imagens
                $this->update_image_product($product, $ProdsJSON, $IDSkuJsonProd);

                $product->setDescription( $ProdsJSON->description );
                $product->setShortDescription( $ProdsJSON->description );

                $product->setData($brand, $ProdsJSON->brand != null ? $this->procAttrConfig($brand, $ProdsJSON->brand->name, 0) : null);
                $product->setData($model, $this->procAttrConfig($model, $ProdsJSON->model, 0));
                $product->setData($video_url, $this->procAttrConfig($video_url, $ProdsJSON->videoURL, 0));
                $product->setData($volume_comprimento, $this->procAttrConfig($volume_comprimento, $ProdsJSON->length, 0));
                $product->setData($volume_altura, $this->procAttrConfig($volume_altura, $ProdsJSON->height, 0));
                $product->setData($volume_largura, $this->procAttrConfig($volume_largura, $ProdsJSON->width, 0));
                $product->setData($warranty_time, $this->procAttrConfig($warranty_time, $ProdsJSON->warrantyTime, 0));
                $product->setData($nbm, $this->procAttrConfig($nbm, $ProdsJSON->nbm, 0));
                $product->setData($nbm_origin, $this->procAttrConfig($nbm_origin, $ProdsJSON->originCode, 0));
                $product->setData($ean, $this->procAttrConfig($ean, $skuEan, 0));
                $product->setData($warranty_text, $this->procAttrConfig($warranty_text, $ProdsJSON->warranty, 0));

                $product->setData($priceField, $skuProd->price);
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                $stockItem->setData('is_in_stock',  $skuProd->stockAmount > 0 ? '1' : '0');
                $stockItem->setData('qty', $skuProd->stockAmount);
                $stockItem->save();

                $product->save();

                $ProdCrt = $product;
            }

        }

        return $ProdCrt;
    }

    public function massUpdtProds(){
        try {
            $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', Mage::app()->getStore()->getId());
            $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', Mage::app()->getStore()->getId());
            $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', Mage::app()->getStore()->getId());

            if($typeSincProd == 0){
                $products = $products = Mage::getModel('catalog/product')
                            ->getCollection()
                            ->addAttributeToSelect('name');
                $cont = 0;
                foreach($products as $product) {
                    $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild( $product->getId() );

                    if (!$parentIds) {
                        $anymarketproductsUpdt =  Mage::getModel('db1_anymarket/anymarketproducts')->load($product->getId(), 'nmp_id');
                        if($anymarketproductsUpdt->getData('nmp_id') == null){

                            $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild( $product->getId() );
                            if (!isset($parentIds[0])) {
                                $name = $product->getName();
                                $sku  = $product->getSku();
                                $IDProd  = $product->getId();

                                $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts');
                                $anymarketproducts->setNmpId( $IDProd );
                                $anymarketproducts->setNmpSku( $sku );
                                $anymarketproducts->setNmpName( $name );
                                $anymarketproducts->setNmpDescError("");
                                $anymarketproducts->setNmpStatusInt("Não integrado (Magento)");
                                $anymarketproducts->setStatus($product->getData('integra_anymarket'));
                                $anymarketproducts->save();

                                $cont = $cont+1;
                            }
                        }else{
                            if( ($anymarketproductsUpdt->getData('nmp_sku') != $product->getSku() ) || ($anymarketproductsUpdt->getData('nmp_name') != $product->getName() ) ){
                                $anymarketproductsUpdt->setNmpSku( $product->getSku() );
                                $anymarketproductsUpdt->setNmpName( $product->getName() );
                                $anymarketproductsUpdt->save();

                                $cont = $cont+1;
                            }
                        }
                    }

                }
            }else{
                $headers = array( 
                    "Content-type: application/json",
                    "Accept: */*",
                    "gumgaToken: ".$TOKEN
                );

                $startRec = 0;
                $countRec = 1;

                while ($startRec <= $countRec) {
                    $returnProd = $this->CallAPICurl("GET", $HOST."/rest/api/v1/products/?start=".$startRec."&pageSize=50", $headers, null);
                    if($returnProd['error'] == '0'){
                        $ProdsJSON = $returnProd['return'];

                        $startRec = $ProdsJSON->pageable->start + $ProdsJSON->pageable->pageSize;
                        $countRec = $ProdsJSON->total;

                        $cont = 0;
                        foreach ($ProdsJSON->content as $product) {
                            $anymarketproductsUpdt =  Mage::getModel('db1_anymarket/anymarketproducts')->load($product->id, 'nmp_id');

                            if($anymarketproductsUpdt->getData('nmp_id') == null){
                                $ProdLoaded = Mage::getModel('catalog/product')->loadByAttribute('id_anymarket', $product->id );
                                if(!$ProdLoaded){
                                    $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts');
                                    $anymarketproducts->setNmpId( $product->id );
                                    $anymarketproducts->setNmpSku( '' );
                                    $anymarketproducts->setNmpName( $product->title );
                                    $anymarketproducts->setNmpDescError("");
                                    $anymarketproducts->setNmpStatusInt("Não integrado (AnyMarket)");
                                    $anymarketproducts->setStatus(0);
                                    $anymarketproducts->save();
                                    $cont = $cont+1;
                                }
                            }
                        }
                    }else{
                        $startRec = 1;
                        $countRec = 0;
                    }

                }
            }

            if($cont > 0){
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Total %d products successfully listed.', $cont)
                );
            }
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('There was an error updating the products.')
            );
            Mage::logException($e);
        }
    }

    public function updatePriceStockAnyMarket($IDProd, $QtdStock, $Price){
        $ImportProdSession = Mage::getSingleton('core/session')->getImportProdsVariable();

        if( $ImportProdSession != 'false' ) {
            $product = Mage::getModel('catalog/product')->load( $IDProd );
            if( ($product->getStatus() == 1) && ($product->getData('integra_anymarket') == 1) ){
                $anymarketproductsUpdt =  Mage::getModel('db1_anymarket/anymarketproducts')->load($product->getId(), 'nmp_id');
                if( ($anymarketproductsUpdt->getData('nmp_status_int') != 'Não integrado (Magento)') ){
                    $sincronize = true;
                    if($product->getVisibility() == 1){ //nao exibido individualmente
                        $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild( $product->getId() );
                        if ($parentIds) {
                            $sincronize = true;
                        }else{
                            $sincronize = false;
                        }
                    }

                    if ($sincronize == true) {
                        if($product->getData('id_anymarket') != ""){
                            $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', Mage::app()->getStore()->getId());
                            $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', Mage::app()->getStore()->getId()); 

                            $headers = array( 
                                "Content-type: application/json",
                                "Accept: */*",
                                "gumgaToken: ".$TOKEN
                            );
                            $params = array(
                                "quantity" => $QtdStock,
                                "cost" => $Price
                            );

                            $skuPut = str_replace(" ", "%20", $product->getSku());
                            $returnProd = $this->CallAPICurl("PUT", $HOST."/rest/api/v1/erp/stock/skuInClient/".$skuPut, $headers, $params);

                            if($returnProd['return'] == ''){
                                $returnProd['return'] = Mage::helper('db1_anymarket')->__('Update Stock and Price');
                                $returnProd['error'] = '0';
                                $returnProd['json'] = '';
                            }
                            $this->saveLogsProds($returnProd, $product);
                        }
                    }
                }
            }
        }

    }

}