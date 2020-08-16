<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class BasicInitialTest extends TestCase
{

    /** @test */
    public function SitemapCategoryAddTest()
    {
        // new object
        $length = 10;
        $tmp_name = 'TMP NAME - ' . substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1, $length);
        $response = $this->call('POST', 'http://localhost/sitemap_categories/'.urlencode($tmp_name));
        
        // check status 201 (added)
        $this->assertEquals(201, $response->status());
        
        // get the result    
        $response_content = json_decode($response->getContent(), true);
        
        // "success":"Item addedd"
        $this->assertEquals("Item addedd", $response_content['success']);
        
        // store in the object
        $sitemap_category_id = $response_content['data']['id'];
        
        $this->MerchantAddTest($sitemap_category_id);
    }
    
    public function MerchantAddTest($sitemap_category_id) {
        // new object
        $length = 10;
        $tmp_name = 'TMP NAME - ' . substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1, $length);
        $tmp_url = 'TMP URL - ' . substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1, $length);
        $tmp_description = 'TMP URL - ' . substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1, $length);
        
        // with wrong sitemap category id - check status 412 (error because the invalid sitemap category id)
        $error_response = $this->call('POST', 'http://localhost/merchants/'.urlencode($tmp_name).'/'.urlencode($tmp_url).'/'.urlencode($tmp_description).'/1');
        $this->assertEquals(412, $error_response->status());
        
        // with valid sitemap category id - check status 412 (error because the invalid sitemap category id)
        $success_response = $this->call('POST', 'http://localhost/merchants/'.urlencode($tmp_name).'/'.urlencode($tmp_url).'/'.urlencode($tmp_description).'/'.$sitemap_category_id);
        $this->assertEquals(201, $success_response->status());
        
        // get the result    
        $response_content = json_decode($success_response->getContent(), true);
        
        // "success":"Item addedd"
        $this->assertEquals("Item addedd", $response_content['success']);
        
        // store in the object
        $merchant_id = $response_content['data']['id'];
        
        // repeat the same, and get a 412 because it already exists
        $error_response = $this->call('POST', 'http://localhost/merchants/'.urlencode($tmp_name).'/'.urlencode($tmp_url).'/'.urlencode($tmp_description).'/'.$sitemap_category_id);
        $this->assertEquals(412, $error_response->status());
        
        $this->SitemapCategoryRemoveTest($sitemap_category_id, $merchant_id);
        
    }
    
    public function SitemapCategoryRemoveTest($sitemap_category_id, $merchant_id)
    {
        // try to remove and get an error because it has a linked merchant
        $error_response = $this->call('DELETE', 'http://localhost/sitemap_categories/'.$sitemap_category_id);
        $this->assertEquals(412, $error_response->status());
        
        // then let's remove the merchant
        $error_response = $this->call('DELETE', 'http://localhost/merchants/'.$merchant_id);
        $this->assertEquals(200, $error_response->status());
        
        // and now the Sitemap category id showld work fine
        $error_response = $this->call('DELETE', 'http://localhost/sitemap_categories/'.$sitemap_category_id);
        $this->assertEquals(200, $error_response->status());
        
    }
}
