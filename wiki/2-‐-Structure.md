## **Structure**

### **Model-View-Controller**

I will follow the basic principle of the Model-View-Controller architecture for the first presentation :

#### **Model**: 

##### **Basic**: 

The Symfony Framework uses the Doctrine ORM and, through its entity and repository system, it builds a database and proposes basic request, which are enough for this application.  
The scripts building the database are inside the "migrations" folder, those are created automatically by the Doctrine ORM.

##### **Data structure Tree**: 

1. zone
   --> product_line

2. product_line
   --> category
   --> incident

3. category
   --> button
   -->| upload

4. button
   -->| upload

5. diplay_option
   -->| upload

6. incident_category
   --> incident

7. doctrine_migration_versions (no relations)

8. messenger_messages (no relations)

9. user (no relations)

##### **TABLES**:

1. button
    - id (Primary Key)
    - category_id (Foreign Key - References category.id)
    - name

2. category
    - id (Primary Key)
    - product_line_id (Foreign Key - References product_line.id)
    - name

3. display_option
    - id (Primary Key)
    - name

4. doctrine_migration_versions
    - version (Primary Key)
    - executed_at
    - execution_time

5. incident
    - id (Primary Key)
    - incident_category_id (Foreign Key - References incident_category.id)
    - product_line_id (Foreign Key - References product_line.id)
    - name
    - uploaded_at
    - active
    - path

6. incident_category
    - id (Primary Key)
    - name

7. messenger_messages
    - id (Primary Key)
    - body
    - headers
    - queue_name
    - created_at
    - available_at
    - delivered_at

8. product_line
    - id (Primary Key)
    - zone_id (Foreign Key - References zone.id)
    - name

9. upload
    - id (Primary Key)
    - button_id (Foreign Key - References button.id)
    - display_option_id (Foreign Key - References display_option.id)
    - filename
    - path
    - expiry_date
    - uploaded_at

10. user
    - id (Primary Key)
    - username
    - roles
    - password

11. zone
    - id (Primary Key)
    - name

##### **RELATIONSHIPS**:

- button.category_id -> category.id
- category.product_line_id -> product_line.id
- incident.incident_category_id -> incident_category.id
- incident.product_line_id -> product_line.id
- product_line.zone_id -> zone.id
- upload.button_id -> button.id
- upload.display_option_id -> display_option.id


#### **View**: 

The views are built around two main technologies : Twig and JavaScript. 
The Twigs template system allows to easily and quickly build new pages using either HTML5, JavaScript or available Twig functions mainly written in PHP. 
Most of the views are client sides in the case of DocAuPoste2.
All the views reside in the /templates/ folder. 
The JavaScript lives inside /assets/js/ folder. Some of the script use the ApiController to access data dynamically. 


##### **Tree of the entire Web App**: 

###### _Specific Module view structure:_

***

_Api controller to generate the views of the forms and the Forms Type file used with the modules :_

***

- api_cascading_dropdown_data :
    - [/src/Controller/ApiController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/ApiController.php)
    - [/src/Form/UploadType.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Form/UploadType.php)
________________________________________________
- api_incidents_cascading_dropdown_data : 
    - [/src/Controller/ApiController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/ApiController.php)
    - [/src/Form/IncidentType.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Form/IncidentType.php)
________________________________________________

_General Incident upload and creation view structure:_

***

- app_generic_upload_incident_files 
    - api_incidents_cascading_dropdown_data :
        - [/templates/services/incidents/cascading_dropdowns_incidents.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/incidents/cascading_dropdowns_incidents.html.twig)
        - [/assets/js/incident-cascading-dropdowns.js](https://github.com/polangres/DocAuPoste2/blob/main/assets/js/incident-cascading-dropdowns.js)
        - [/src/Controller/ApiController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/ApiController.php)
    - app_incident_incidentsCategory_creation :
        - [/templates/services/incidents/cascading_dropdowns_incidents.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/incidents/cascading_dropdowns_incidents.html.twig)
        - [/assets/js/incident-cascading-dropdowns.js](https://github.com/polangres/DocAuPoste2/blob/main/assets/js/incident-cascading-dropdowns.js)
    - app_incident_incidentsCategory_deletion :
        - [/templates/services/incidents/cascading_dropdowns_incidents.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/incidents/cascading_dropdowns_incidents.html.twig)
        - [/assets/js/incident-cascading-dropdowns.js](https://github.com/polangres/DocAuPoste2/blob/main/assets/js/incident-cascading-dropdowns.js)
    - app_incident_download_file :
        - [/templates/services/incidents/incidents_view.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/incidents/incidents_view.html.twig)
    - app_incident_delete_file :
        - [/templates/services/incidents/incidents.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/incidents/incidents.html.twig)
    - app_incident_modify_file :
        - [/templates/services/incidents/incidents_modification.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/incidents/incidents_modification.html.twig)
        - api_incidents_cascading_dropdown_data :
            - [/templates/services/incidents/cascading_dropdowns_incidents.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/incidents/cascading_dropdowns_incidents.html.twig)
            - [/assets/js/incident-cascading-dropdowns.js](https://github.com/polangres/DocAuPoste2/blob/main/assets/js/incident-cascading-dropdowns.js)
            - [/src/Controller/ApiController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/ApiController.php)
- app_mandatory_incident : 
            - [/templates/services/incidents/incidents_view.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/incidents/incidents_view.html.twig)
________________________________________________

_General account creation view structure:_

***

- account_creation : 
    - [/templates/services/accountservices/create_account.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/accountservices/create_account.html.twig)
    - [/templates/services/accountservices/role_affectation.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/accountservices/role_affectation.html.twig)
    - app_modify_account_view
        - app_modify_account :
             - [/templates/services/accountservices/modify_account_view.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/accountservices/modify_account_view.html.twig)
    - app_delete_account 
         - [/templates/services/accountservices/create_account.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/accountservices/create_account.html.twig)
________________________________________________

_General Upload and creation view structure:_

***

- app_generic_upload_files : 
    - [/templates/services/uploads/upload.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/uploads/upload.html.twig)
        - api_cascading_dropdown_data :
             - [/assets/js/cascading-dropdowns.js](https://github.com/polangres/DocAuPoste2/blob/main/assets/js/cascading-dropdowns.js)
             - [/src/Controller/ApiController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/ApiController.php)
    - app_uploaded_files : 
        - [/templates/services/uploads/upload_list.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/uploads/upload_list.html.twig)
    - app_download_file : 
        - [/src/Controller/UploadController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/UploadController.php)
    - app_delete_file : 
        - [/templates/services/uploads/upload_list.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/uploads/upload_list.html.twig)
    - app_modify_file : 
        - [/templates/services/uploads/upload_list.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/uploads/upload_list.html.twig)
        - api_cascading_dropdown_data : 
             - [/assets/js/cascading_dropdowns.js](https://github.com/polangres/DocAuPoste2/blob/main/assets/js/cascading_dropdowns.js)
             - [/src/Controller/ApiController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/ApiController.php)
_______________________________________________

###### _General View structure of the entire Application:_

***

- app_base : 
    - [/templates/base.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/base.html.twig)
    - app_login : 
        - [/templates/security/login.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/security/login.html.twig)
    - app_logout
    - app_tutorial : 
        - [/templates/tutorial/tutorial_index.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/tutorial/tutorial_index.html.twig)
    - app_create_super_admin : 
        - [/templates/services/accountservices/create_account.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/accountservices/create_account.html.twig)
    - app_super_admin : 
        - [/templates/super_admin/super_admin_index.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/super_admin/super_admin_index.html.twig)
        - app_super_admin_create_admin : 
            - app_modify_account_view :
                - app_modify_account : 
            - app_delete_account
        - app_super_admin_create_zone : 
            - [/templates/super_admin/super_admin_index.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/super_admin/super_admin_index.html.twig)
        - app_super_admin_delete_zone : 
            - [/templates/super_admin/super_admin_index.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/super_admin/super_admin_index.html.twig)
        - app_generic_upload_files
                - api_cascading_dropdown_data
            - app_uploaded_files
            - app_download_file
            - app_delete_file
            - app_modify_file
                - api_cascading_dropdown_data
        - app_generic_upload_incident_files
            - api_incidents_cascading_dropdown_data
            - app_incident_incidentsCategory_creation
            - app_incident_incidentsCategory_deletion
            - app_incident_download_file
            - app_incident_delete_file
            - app_incident_modify_file
                - api_incidents_cascading_dropdown_data
    - app_zone : 
        - [/templates/zone.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/zone.html.twig)
        - app_zone_admin : 
            - [/templates/zone_admin/zone_admin_index.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/zone_admin/zone_admin_index.html.twig)
            - app_zone_admin_create_line_admin
                - app_modify_account_view
                    - app_modify_account
                - app_delete_account
            - app_zone_admin_create_productline : 
                - [/templates/zone_admin/zone_admin_index.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/zone_admin/zone_admin_index.html.twig)
            - app_zone_admin_delete_productline : 
                - [/templates/zone_admin/zone_admin_index.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/zone_admin/zone_admin_index.html.twig)
            - app_generic_upload_files
                    - api_cascading_dropdown_data
                - app_uploaded_files
                - app_download_file
                - app_delete_file
                - app_modify_file
                    - api_cascading_dropdown_data
        - app_productline : 
            - [/templates/productline.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/productline.html.twig)
        - app_mandatory_incident : 
            - [/templates/services/incidents/incidents_view.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/services/incidents/incidents_view.html.twig)
            - app_productline_admin : 
                - [/templates/productline_admin/productline_admin.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/productline_admin/productline_admin.html.twig)
                - app_productline_admin_create_manager
                    - app_modify_account_view
                        - app_modify_account
                    - app_delete_account
                - app_productline_admin_create_category : 
                    - [/templates/productline_admin/productline_admin.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/productline_admin/productline_admin.html.twig)
                - app_productline_admin_delete_category :
                    - [/templates/productline_admin/productline_admin.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/productline_admin/productline_admin.html.twig)
                - app_generic_upload_files
                        - api_cascading_dropdown_data
                    - app_uploaded_files
                    - app_generic_upload_files
                        - api_cascading_dropdown_data
                    - app_download_file
                    - app_delete_file
                    - app_modify_file
                        - api_cascading_dropdown_data
                - app_generic_upload_incident_files
                    - api_incidents_cascading_dropdown_data
                    - app_incident_incidentsCategory_creation
                    - app_incident_incidentsCategory_deletion
                    - app_incident_download_file
                    - app_incident_delete_file
                    - app_incident_modify_file
                        - api_incidents_cascading_dropdown_data
            - app_category : 
                - [/templates/category.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/category.html.twig)
                - app_category_admin : 
                    - [/templates/category_admin/category_admin_index.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/category_admin/category_admin_index.html.twig)
                    - app_category_admin_create_user
                        - app_modify_account_view
                            - app_modify_account
                        - app_delete_account
                    - app_category_admin_create_button : 
                        - [/templates/category_admin/category_admin_index.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/category_admin/category_admin_index.html.twig)
                    - app_category_admin_delete_button : 
                        - [/templates/category_admin/category_admin_index.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/category_admin/category_admin_index.html.twig)
                    - app_generic_upload_files
                            - api_cascading_dropdown_data
                        - app_uploaded_files
                        - app_generic_upload_files
                            - api_cascading_dropdown_data
                        - app_download_file
                        - app_delete_file
                        - app_modify_file
                            - api_cascading_dropdown_data
                    - app_generic_upload_incident_files
                        - api_incidents_cascading_dropdown_data
                        - app_incident_incidentsCategory_creation
                        - app_incident_incidentsCategory_deletion
                        - app_incident_download_file
                        - app_incident_delete_file
                        - app_incident_modify_file
                            - api_incidents_cascading_dropdown_data
                - app_button : 
                    - [/templates/button.html.twig](https://github.com/polangres/DocAuPoste2/blob/main/templates/button.html.twig)
                    - app_download_file : 
                        - [/src/Controller/UploadController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/UploadController.php)
                    - app_generic_upload_files 

#### **Controller**: 

##### **Tree of the different features**: 


***

###### User Account creation:

***

From the view described earlier, used at different level of the application : 

The method resides in each of the Controller managing the mother page : 
- app_create_super_admin : 
    - [/src/Controller/FrontController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/FrontController.php)
    - app_super_admin_create_admin : 
        - [/src/Controller/SuperAdminController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/SuperAdminController.php)
        - app_zone_admin_create_line_admin :
            - [/src/Controller/ZoneAdminController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/ZoneAdminController.php)
            - app_productline_admin_create_manager :
                - [/src/Controller/ProductLineAdminController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/ProductLineAdminController.php)
                - app_category_admin_create_user :
                    - [/src/Controller/CategoryAdminController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/CategoryAdminController.php)
- ⇒ Each of those methods passes their request to the service createAccount in the AccountService : 
    - [/src/Service/AccountService.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Service/AccountService.php)


Integrated into those views, resides the modification account view and system : 
- app_modify_account_view : 
    - app_modify_account
        - [/src/Controller/SecurityController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/SecurityController.php)
- ⇒ The method passes its request to the service modifyAccount in the AccountService : 
    - [/src/Service/AccountService.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Service/AccountService.php)

- app_login
- app_logout
- ⇒ Those methods concerning does two systems also reside inside the SecurityController.php and they use the authenticate function of the AppCustomAuthenticator.php security system:
     - [/src/Controller/SecurityController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/SecurityController.php)
     - [/src/Security/AppCustomAuthenticator.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Security/AppCustomAuthenticator.php)


***

###### Organization Entity creation:

***

From the view described earlier, used at different level of the application : 

The method resides in each of the Controller managing the mother page :
- app_super_admin_create_zone - app_super_admin_delete_zone : 
    - [/src/Controller/SuperAdminController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/SuperAdminController.php)
    - app_zone_admin_create_productline - app_zone_admin_delete_productline : 
        - [/src/Controller/ZoneAdminController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/ZoneAdminController.php)
        - app_productline_admin_create_category - app_productline_admin_delete_category :
            - [/src/Controller/ProductLineAdminController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/ProductLineAdminController.php)
            - app_category_admin_create_button - app_category_admin_delete_button : 
                - [/src/Controller/CategoryAdminController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/CategoryAdminController.php)
- ⇒ Each of the creation methods lives entirely in those controllers.
- ⇒ To ensure the complete deletion of all hereditary objects, the deletion methods utilize the deleteEntity function provided by the EntityDeletionService.php service:
    - [/src/Service/EntityDeletionService.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Service/EntityDeletionService.php)

_The Folder Creation and delete systems:_

This system is integrated into each Organization Entity creation method methods.
- ⇒ Each of the methods use the folderStructure function present in the FolderCreationService.php.
The folder deletion system is only integrated to the entityDeletionService.php function deleteEntity
- ⇒The deleteFolderStructure does still reside inside the FolderCreationService.php.
    - [/src/Service/FolderCreationService.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Service/FolderCreationService.php)

***

###### Document Upload systems:

***

From the view described earlier, used at different level of the application : 

The methods reside in one controller, whereas the views are present in all the Admin pages and the button page to make it easier for the user to manage the document upload system. 

- app_generic_upload_files  
    - app_uploaded_files  
    - app_download_file
    - app_delete_file
    - app_modify_file
    - [/src/Controller/UploadController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/UploadController.php)

- ⇒ The creation method uses the uploadFiles function from the UploadsService.php service.
- ⇒ The deletion method uses the deleteFiles function from the UploadsService.php service.
    - [/src/Service/UploadsService.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Service/UploadsService.php)

- ⇒ The modification method uses the modifyFiles function from the UploadsService.php service, but also makes use of the ApiController.php and the UploadType.php Form :
    - [/src/Controller/ApiController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/ApiController.php)
    - [/src/Form/UploadType.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Form/UploadType.php)

- ⇒ The download method resides only on the controller.
- ⇒ The uploaded method resides only on the controller and is only a renderer.

_The listing by parents entity and grouping of the uploads:_

This part concerns two functions that find their usefulness, organizing the uploads to be clearly and appropriately displayed on the Admin page inside the uploads views. 
- ⇒ The groupUploads residing inside the UploadsService.php. It groups uploads so that they appear inside some kind of logic organization.
    - [/src/Service/UploadsService.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Service/UploadsService.php)
- ⇒ The uploadsByParentEntity residing inside the EntityHeritanceService.php. It filters the uploads, displaying only the uploads of the correct organization entity concerned by the current Admin page.
    - [/src/Service/EntityHeritanceService.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Service/EntityHeritanceService.php)


***

###### Incident file upload systems:

***


From the view described earlier, used at different level of the application : 

The methods reside in one controller, whereas the views are present in all the Admin pages to make it easier for the user to manage the Incident file upload system. 

- app_generic_upload_incident_files
    - app_incident_incidentsCategory_creation
    - app_incident_incidentsCategory_deletion
    - app_incident_download_file
    - app_incident_delete_file
    - app_incident_modify_file
    - app_mandatory_incident
    - [/src/Controller/IncidentController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/IncidentController.php)

- ⇒ The creation method uses the uploadIncidentFiles function from the IncidentsService.php service.
- ⇒ The deletion method uses the deleteIncidentFiles function from the IncidentsService.php service.
    - [/src/Service/IncidentsService.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Service/IncidentsService.php)

- ⇒ The modification method uses the modifyIncidentFiles function from the IncidentsService.php service, but also makes use of the ApiController.php and the IncidentType.php Form :
    - [/src/Controller/ApiController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/ApiController.php)
    - [/src/Form/IncidentType.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Form/IncidentType.php)

- ⇒ The download method resides only on the controller.
- ⇒ The IncidentCategory creation method resides only on the controller.
- ⇒ The IncidentCategory deletion method resides only on the controller.

- ⇒ The Mandatory_Incident method resides only on the controller. It checks if an incident is present and forces for it to be displayed. 

***

###### Tutorial:

***

The tutorial does have a dedicated controller : 
- [/src/Controller/TutorialController.php](https://github.com/polangres/DocAuPoste2/blob/main/src/Controller/TutorialController.php)