<?php
/**
 * @file deshape_helper.php
 * @brief FABtotum helper function
 * 
 * @author Krios Mane <km@fabtotum.com>
 * @author Daniel Kesler <dk@fabtotum.com>
 * @version 0.1
 * @copyright https://opensource.org/licenses/GPL-3.0
 * 
 */

if(!function_exists('sync_projects'))
{
    /**
     * sync database with remote projects
     * @param $fabid string
     * @param $access_token string 
     * 
     */
    function sync_projects($fabid, $access_token)
    {
        $CI =& get_instance();   
        /**
         * retrieve remote projects
         */
        $config['token'] = $access_token;
        $CI->load->library('Deshape', $config);
        
        $projects = $CI->deshape->list_projects_full();
        
        /**
         * loop trought the projects
         * check if localy exists
         * .if not exists copy locally
         * .if exists
         *     .check if needs to be updated
         */
        if($projects['status'] == true){
            
            //load models
            $CI->load->model('ProjectsModel', 'projects');
            
            foreach($projects['data'] as $project){
                
                if(!$CI->projects->exists($project['project_id'])){
                    
                    /**
                     * add project
                     */
                    $data = array(
                        'deshape_id'  => $project['project_id'],
                        'fabid'       => $fabid,
                        'sku'         => $project['project_sku'],
                        'name'        => $project['project_name'],
                        'description' => $project['project_description'],
                        'visibility'  => $project['visibility'],
                        'categories'  => implode(',', $project['categories']),
                        'image_url'   => $project['image_url']
                    );
 
                    //print_r($data);
                    $id_project = $CI->projects->add($data);
                    unset($data);
                    /**
                     * add parts
                     */
                    if(count($project['parts']) > 0){
                        
                        $CI->load->model('Parts', 'parts');
                        
                        foreach($project['parts'] as $part){
                            
                            $data = array(
                                'name'           => $part['part_name'],
                                'description'    => $part['part_description'],
                                'price'          => $part['price'],
                                'creation_tool'  => $part['part_creation_tool'],
                                'quantity'       => $part['part_quantity'],
                                'ordinal_number' => $part['ordinal_number'],
                                'sku'            => $part['part_sku'],
                                'deshape_id'     => $part['part_id']
                            );
                            
                            //print_r($data);
                            $id_part = $CI->parts->add($data);
                            unset($data);
                            /**
                             * @TODO assoc part to project
                             */
                            $CI->projects->add_part($id_project, $id_part);
                            
                            /**
                             * add files
                             */
                            if(count($part['part_files']) > 0){
                                
                                $CI->load->model('Deshapefiles', 'files');
                                
                                foreach($part['part_files'] as $file){
                                    
                                    $data = array(
                                        'deshape_id' => $file['file_id'],
                                        'title'      => $file['title'],
                                        'type'       => $file['file_type'],
                                        'name'       => $file['file_name']                                        
                                    );
                                    
                                    $id_file = $CI->files->add($data);
                                    unset($data);
                                    /**
                                     * @TODO assoc file to part
                                     */
                                    $CI->parts->add_file($id_part, $id_file);
                                    
                                }
                            }
                            
                        }
                    }
                }
                
            }
        }
        
    }
}