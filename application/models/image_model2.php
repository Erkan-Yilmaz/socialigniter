<?php

class Image_model2 extends CI_Model 
{

	function get_external_image($image, $image_path)
	{	
	    $ch = curl_init ($image);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
	    $rawdata = curl_exec($ch);
	    curl_close ($ch);
	    if(file_exists($image_path)){
	        unlink($image_path);
	    }
	    $fp = fopen($image_path,'x');
	    fwrite($fp, $rawdata);
	    fclose($fp);
	}

	function make_profile_images($upload_file, $upload_width, $upload_height, $user_id)
	{
	
	
		$this->load->helper('file');
	    $this->load->library('image_lib');		
				
		make_folder(config_item('users_images_folder').$user_id);		
		delete_files(config_item('users_images_folder').$user_id."/");
	    
	    $raw_path 			= config_item('uploads_folder').$upload_file;
	    $thumb_path 		= config_item('users_images_folder').$user_id."/".$upload_file;
	    $square_path 		= config_item('users_images_folder').$user_id."/square_".$upload_file;
		$image_path			= $raw_path;
	    
	    $original_width		= 0;
	    $original_height	= 0;
	    
	    
	    log_message('debug', 'upload_paths_start: '.$image_path);
	    
	    
	    // Raw width larger than allowed
		if ($upload_width >= config_item('users_images_full_width'))
		{
			$original_width = config_item('users_images_full_width');
		}
		else
		{
			$original_width = $upload_width;
		}

	    // Raw height larger than allowed
		if ($upload_height >= config_item('users_images_full_height'))
		{
			$original_height = config_item('users_images_full_height');
		}
		else
		{
			$original_height = $upload_height;
		}
	       
	       	       
	       
	       
	    // Horizontal or Vertical Picture	    
	    if($upload_width > $upload_height)
	    {
	        $res_width 		= $original_width; 
	        $res_height 	= $original_height; 
	        $set_master_dim = 'width';
	    }
	    else
	    {
	        $res_width 		= $original_width;
	        $res_height 	= $original_height;
	        $set_master_dim = 'height';
	    }
	    
	    
	    
	    // DOES SQUARE IMAGE
	    //
	    // Calculate Offset
	    if($upload_width > $upload_height)
	    {
	    	// Horizontal picture
	        $diff = $upload_width - $upload_height;
	        $cropsize = $upload_height - 1;
	        $x_axis = round($diff / 2);
	        $y_axis = 0;
	    }
	    else
	    {
	    	// Vertical picture
	        $cropsize = $upload_width - 1;
	        $diff = $upload_height - $upload_width;
	        $x_axis = 0;
	        $y_axis = round($diff / 2);
	    }
	    	    
	    
	    // Largest Possible Square Image	 
		$crop_config['image_library']	= 'gd2';
	    $crop_config['source_image'] 	= $raw_path;
	    $crop_config['maintain_ratio']	= FALSE;
	    $crop_config['new_image'] 		= config_item('users_images_folder').$user_id."/square_".$upload_file; 
	    $crop_config['x_axis']		 	= $x_axis;
	    $crop_config['y_axis'] 			= $y_axis;
	    $crop_config['width'] 			= $cropsize;
	    $crop_config['height'] 			= $cropsize;
	        
	    $this->image_lib->initialize($crop_config);
	
	    if (!$this->image_lib->crop())
	    {
	        echo "error croping";
	        echo $this->image_lib->display_errors();
	        return false;
	    }	    
  
  	    $this->image_lib->clear();  	    
  	    


		// Full image crop resize
		if (config_item('users_images_sizes_full') == 'yes')
		{  	    
			if (config_item('users_images_full_width') == config_item('users_images_full_height')) $image_path = $square_path;
		
		    $resize_config['image_library'] 	= 'gd2';
		    $resize_config['source_image'] 		= $image_path;
		    $resize_config['maintain_ratio'] 	= TRUE;
		    $resize_config['new_image'] 		= config_item('users_images_folder').$user_id."/full_".$upload_file;	    
		    $resize_config['width'] 			= $res_width;
		    $resize_config['height'] 			= $res_height;
		    $resize_config['master_dim'] 		= $set_master_dim;
		    	   
		   	$this->image_lib->initialize($resize_config);
		    
		    if (!$this->image_lib->resize())
		    {
		        echo "error first resize";
		        echo $this->image_lib->display_errors();
		        return false;
		    }
		    
		    $this->image_lib->clear();
	  	}


		// Large image crop resize
		if (config_item('users_images_sizes_large') == 'yes')
		{
			if (config_item('users_images_large_width') == config_item('users_images_large_height')) $image_path = $square_path;
		
		    $thumb_config['image_library'] 		= 'gd2';
		    $thumb_config['source_image'] 		= $image_path;
		    $thumb_config['maintain_ratio'] 	= TRUE;
		    $thumb_config['new_image']			= config_item('users_images_folder').$user_id."/"."large_".$upload_file;
		    $thumb_config['width'] 				= config_item('users_images_large_width');
		    $thumb_config['height'] 			= config_item('users_images_large_height');
		    $thumb_config['master_dim'] 		= $set_master_dim;		    
		    
		    $this->image_lib->initialize($thumb_config);
		    
		    if (!$this->image_lib->resize()) {
		        echo "error resize croping";
		        echo $this->image_lib->display_errors();
		        return false;
		    }
	
		    $this->image_lib->clear();
		}



		// Medium image crop resize
		if (config_item('users_images_sizes_medium') == 'yes')
		{
			if (config_item('users_images_medium_width') == config_item('users_images_medium_height')) $image_path = $square_path;
		
		    $thumb2_config['image_library'] 	= 'gd2';
		    $thumb2_config['source_image'] 		= $image_path;
		    $thumb2_config['maintain_ratio'] 	= TRUE;
		    $thumb2_config['new_image']			= config_item('users_images_folder').$user_id."/"."medium_".$upload_file;
		    $thumb2_config['width'] 			= config_item('users_images_medium_width');
		    $thumb2_config['height'] 			= config_item('users_images_medium_height');
		    $thumb2_config['master_dim'] 		= $set_master_dim;

		    
		    $this->image_lib->initialize($thumb2_config);
		    
		    if (!$this->image_lib->resize()) {
		        echo "error resize croping";
		        echo $this->image_lib->display_errors();
		        return false;
		    }
	
		    $this->image_lib->clear();
		}
		


		// Small image crop resize
		if (config_item('users_images_sizes_small') == 'yes')
		{
			if (config_item('users_images_small_width') == config_item('users_images_small_height')) $image_path = $square_path;
		
		    $thumb3_config['image_library'] 	= 'gd2';
		    $thumb3_config['source_image'] 		= $image_path;
		    $thumb3_config['maintain_ratio'] 	= TRUE;
		    $thumb3_config['new_image']			= config_item('users_images_folder').$user_id."/"."small_".$upload_file;
		    $thumb3_config['width'] 			= config_item('users_images_small_width');
		    $thumb3_config['height'] 			= config_item('users_images_small_height');
		    $thumb3_config['master_dim'] 		= $set_master_dim;
		    
		    $this->image_lib->initialize($thumb3_config);
		    
		    if (!$this->image_lib->resize())
		    {
		        echo "error resize croping";
		        echo $this->image_lib->display_errors();
		        return false;
		    }
		}
	    

		// Medium image crop resize
		if (config_item('users_images_sizes_original') == 'no')
		{	    
	    	//unlink($thumb_path);
	    }
	    
	    unlink($square_path); 
	    	    
	    return true;	    
	}
	
}