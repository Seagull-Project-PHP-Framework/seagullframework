<?php
/**
 * Array of all Manager Panel options depending on context
 * Allows to build a module header with actions, linked managers ...
 *
 * @package SGL
 * @author  Julien Casanova <julien_casanova@yahoo.fr>
 */

 // ATTENTION l'intégralité du fichier étant inclus quelque soit le manager dans lequel on se trouve, certaines variables peuvent être inexistantes. Ex : $input->category_id si on n'est pas dans CategoryMgr.
 // IL FAUT DONC déclarer ses variables au préalables en attendant de faire mieux.
if(!isset($input->category_id)) $input->category_id = 1;
if(!isset($input->moduleId)) $input->moduleId = '';
 // FIN de la déclaration des variables.
$aMgrOptions = array
(
'admin' => array
    (
    'adminmenu' => array
        (
        'all' => array
            (
            'pageTitle'     => 'Admin Menu Manager',
            'instructions'  => '',
            'manage'        => '',
            'actions'       => '',
            ),
        'list' => array
            (
            'pageTitle'     => 'Admin Menu Manager :: Browse',
            'instructions'  => 'Parcourez la liste des sections du menu administrateur puis éditer ou supprimer les.<br />Cliquez sur "Nouvelle section" pour ajouter un élément au menu.',
            'manage'        => '',
            'actions'       => array
                (
                'New section' => SGL_Url::makeLink('add','adminmenu','admin',array(),'frmCatID|0'),
                ),
            ),
        'add' => array
            (
            'pageTitle'     => 'Admin Menu Manager :: Add',
            'instructions'  => 'Renseignez les champs de ce formulaire pour créer une nouvelle section dans le menu "Administrateur". Celle-ci ne sera visible que par les groupes auxquels vous donnez les droits d\'accés.',
            'manage'        => '',
            'actions'       => '',
            ),
        'edit' => array
            (
            'pageTitle'     => 'Admin Menu Manager :: Edit',
            'instructions'  => 'Renseignez les champs de ce formulaire pour modifier la section courante dans le menu "Administrateur". Celle-ci ne sera visible que par les groupes auxquels vous donnez les droits d\'accés.',
            'manage'        => '',
            'actions'       => '',
            ),
        'update' => array
            (
            'pageTitle'     => 'Admin Menu Manager :: Edit',
            'instructions'  => 'Renseignez les champs de ce formulaire pour modifier la section courante dans le menu "Administrateur". Celle-ci ne sera visible que par les groupes auxquels vous donnez les droits d\'accés.',
            'manage'        => '',
            'actions'       => '',
            ),
        )
    ),
'block' => array
    (
    'block' => array
        (
        'all' => array
            (
            'pageTitle'     => 'Blocks Manager',
            'instructions'  => '',
            'manage'        => '',
            'actions'       => '',
            ),
        'list' => array
            (
            'pageTitle'     => 'Blocks Manager :: Browse',
            'instructions'  => 'Sélectionnez les blocks que vous voulez afficher dans les différentes sections du site. Puis configurez les droits d\'affichage et les sections.',
            'manage'        => '',
            'actions'       => array
                (
                'New block' => SGL_Url::makeLink('add','block','block'),
                'New Html block' => SGL_Url::makeLink('addDynamic','block','block'),
                ),
            ),
        'add' => array
            (
            'pageTitle'     => 'Blocks Manager :: New block',
            'instructions'  => 'Utilisez ce formulaire pour saisir les informations relatives au nouveau bloc.',
            'manage'        => '',
            'actions'       => array
                (
                'Back' => SGL_Url::makeLink('list','block','block'),
                ),
            ),
        'addDynamic' => array
            (
            'pageTitle'     => 'Blocks Manager :: New Html block',
            'instructions'  => 'Utilisez ce formulaire pour saisir les informations relatives au nouveau bloc.',
            'manage'        => '',
            'actions'       => array
                (
                'Back' => SGL_Url::makeLink('list','block','block'),
                ),
            ),
        'edit' => array
            (
            'pageTitle'     => 'Blocks Manager :: Edit',
            'instructions'  => 'Utilisez ce formulaire pour modifier les informations relatives au bloc.',
            'manage'        => '',
            'actions'       => '',
            ),
        'update' => array
            (
            'pageTitle'     => 'Blocks Manager :: Edit',
            'instructions'  => 'Utilisez ce formulaire pour modifier les informations relatives au bloc.',
            'manage'        => '',
            'actions'       => '',
            ),
        'reorder' => array
            (
            'pageTitle'     => 'Blocks Manager :: Reorder blocks',
            'instructions'  => 'Pour modifier l\'ordre dans lequel apparaissent les blocs dans les colonnes gauche/droite, il suffit de sélectionner un bloc puis de cliquer sur les flèches "Monter" ou "Descendre".',
            'manage'        => '',
            'actions'       => array
                (
                'Back' => SGL_Url::makeLink('list','block','block'),
                ),
            ),
        ),
    ),
'contactus' => array
    (
    
    ),
'default' => array
    (
    'bug' => array
        (
        'list' => array
            (
            'pageTitle'     => 'Bug Report',
            'instructions'  => 'Found a bug? Please fill out and submit the form below - help us make Seagull better software. <br /> -- Thanks',
            ),
        'send' => array
            (
            'pageTitle'     => 'Bug Report',
            'instructions'  => 'Found a bug? Please fill out and submit the form below - help us make Seagull better software. <br /> -- Thanks',
            ),
        ),
    'config' => array
        (
        'all' => array
            (
            'pageTitle'     => 'Config Manager',
            'instructions'  => 'Please use the following form to edit your config file',
            'actions'       => array
                (
                'Cancel' => SGL_Url::makeLink('edit','config','default'),
                'Save' => 'javascript:document.conf.submit()',
                ),
            ),
        ),
    'module' => array
        (
        'all' => array
            (
            'pageTitle'     => 'Module Manager',
            'instructions'  => '',
            'manage'        => '',
            'actions'       => '',
            ),
        'list' => array
            (
            'pageTitle'     => 'Module Manager',
            'instructions'  => 'Pour modifier les attributs d\'un module, cliquez sur son icône. Vous pourrez prochainement désinstaller un module depuis cet écran.',
            'manage'        => '',
            'actions'       => array
                (
                'View modules' => SGL_Url::makeLink('overview','module','default'),
                'Add a module' => SGL_Url::makeLink('add','module','default'),
                ),
            ),
        'overview' => array
            (
            'pageTitle'     => 'Module Manager',
            'instructions'  => 'Voici la liste des modules disponibles.<br />Pour accéder à la partie administration d\'un module, cliquez sur son icône. Si le lien n\'existe pas, cela veut dire que le module n\'est pas configurable.',
            'manage'        => '',
            'actions'       => array
                (
                'Add module' => array(SGL_Url::makeLink('add','module','default'), SGL_ADMIN),
                ),
            ),
        'insert' => array
            (
            'pageTitle'     => 'Module Manager :: Add',
            'instructions'  => 'Utilisez ce formulaire pour inscrire un module dans le registre (BDD).<br />Pour créer un nouveau module, utilisez le formulaire de <a href="' . SGL_Url::makeLink('','maintenance','maintenance').'">Maintenance</a>',
            'manage'        => '',
            'actions'       => array
                (
                'Save' => 'javascript:formSubmit("module")',
                'Delete' => 'javascript:formSubmit("module","action","delete")',
                'Cancel' => SGL_Url::makeLink('overview','module','default'),
                ),
            ),
        'update' => array
            (
            'pageTitle'     => 'Module Manager :: Edit',
            'instructions'  => 'Ce formulaire vous permet de modifier les informations sur un module. Vous pouvez modifier son icône et décider s\'il est possible de le configurer ou non.',
            'manage'        => '',
            'actions'       => array
                (
                'Save' => 'javascript:formSubmit("module")',
                'Delete' => 'javascript:formSubmit("module","action","delete")',
                'Cancel' => SGL_Url::makeLink('overview','module','default'),
                ),
            ),
        ),
    'maintenance' => array
        (
        'all' => array
            (
            'pageTitle'     => 'Maintenance Manager',
            'instructions'  => 'instructions_maintenance_all',
            'manage'        => '',
            'actions'       => '',
            ),
        )
    ),
'documentor' => array
    (
    
    ),
'export' => array
    (
    
    ),
'faq' => array
    (
    'faq' => array
        (
        'all' => array
            (
            'pageTitle'     => 'FAQ Manager',
            'instructions'  => '',
            'manage'        => '',
            'actions'       => '',
            ),
        'list' => array
            (
            'pageTitle'     => 'FAQ Manager',
            'instructions'  => 'Utilisez ce module pour proposer une liste de FAQ ("Foire Aux Questions / Frequently Asked Questions") à vos utilisateurs. Vous pouvez créer de nouveaux éléments et les réordonner.',
            'manage'        => '',
            'actions'       => array
                (
                'New FAQ' => SGL_Url::makeLink('add','faq'),
                'Reorder' => SGL_Url::makeLink('reorder','faq'),
                ),
            ),
        'add' => array
            (
            'pageTitle'     => 'FAQ Manager :: Add',
            'instructions'  => 'Utilisez ce formulaire pour ajouter une nouvelle question dans la liste des FAQ.<br />Il suffit d\'entrer la question et la réponse qui va avec.',
            'manage'        => '',
            'actions'       => array
                (
                'Back' => SGL_Url::makeLink('list', 'faq'),
                ),
            ),
        'edit' => array
            (
            'pageTitle'     => 'FAQ Manager :: Edit',
            'instructions'  => 'Utilisez ce formulaire pour modifier une question et sa réponse.',
            'manage'        => '',
            'actions'       => '',
            ),
        'reorder' => array
            (
            'pageTitle'     => 'FAQ Manager :: Reorder',
            'instructions'  => 'Pour modifier l\'ordre d\'affichage des FAQ il suffit de sélectionner un élément puis de cliquer sur la flèche "monter" ou "descendre". N\'oubliez pas de cliquer sur "Sauvegarder" une fois les modifications effectuées.',
            'manage'        => '',
            'actions'       => array
                (
                'Back' => SGL_Url::makeLink('list', 'faq'),
                ),
            ),
        )
    ),
'guestbook' => array
    (
    
    ),
'maintenance' => array
    (
    'maintenance' => array
        (
        'all' => array
            (
            'pageTitle'     => 'Maintenance Manager',
            'instructions'  => 'Ce module est réservé à l\'administrateur du site.<br />Si vous n\'êtes pas sûr de ce que vous faites, n\'effectuez aucune action sur cette page.',
            'manage'        => '',
            'actions'       => '',
            ),
        )
    ),
'messaging' => array
    (
    
    ),
'navigation' => array
    (
    'category' => array
        (
        'all' => array
            (
            'pageTitle'     => 'Category Manager',
            'instructions'  => '',
            'manage'       => array
                (
                'Categories' => SGL_Url::makeLink('', 'category', 'navigation', array(), "frmCatID|{$input->category_id}"),
                'Articles' => SGL_Url::makeLink('','article','publisher'),
                'Documents' => SGL_Url::makeLink('','document','publisher'),
                ),
            'actions'       => array
                (
                'Add Category' => SGL_Url::makeLink('insert','category','navigation',array(),"frmCatID|{$input->category_id}"),
                'Reorder Categories' => SGL_Url::makeLink('reorder','category','navigation'),
                'Add Root Category' => SGL_Url::makeLink('insert','category','navigation',array(),'frmCatID|0'),
                ),
            ),
        'list' => array
            (
            'pageTitle'     => 'Category Manager',
            'instructions'  => 'Sélectionnez une catégorie dans la liste de gauche pour la modifier.<br />Vous pouvez ajouter des catégories dans l\'arborescence en cliquant sur "Ajouter une catégorie".',
            'manage'        => '',
            'actions'       => '',
            ),
        'reorder' => array
            (
            'pageTitle'     => 'Category Manager :: Reorder',
            'instructions'  => 'Pour déplacer une catégorie dans l\'arborescence, il suffit de cliquer sur la flèche correspondante (haut ou bas). Si vous désirez modifier la hiérarchie des catégories (c\'est à dire changer la catégorie parente), cliquez sur "Editer" et sélectionnez la nouvelle "Catégorie Parente".',
            'manage'        => '',
            'actions'       => '',
            ),
        'reorderUpdate' => array
            (
            'pageTitle'     => 'Category Manager :: Reorder',
            'instructions'  => 'Pour déplacer une catégorie dans l\'arborescence, il suffit de cliquer sur la flèche correspondante (haut ou bas). Si vous désirez modifier la hiérarchie des catégories (c\'est à dire changer la catégorie parente), cliquez sur "Editer" et sélectionnez la nouvelle "Catégorie Parente".',
            'manage'        => '',
            'actions'       => '',
            ),
        ),
    'page' => array
        (
        'all' => array
            (
            'pageTitle'     => 'Page Manager',
            'instructions'  => '',
            'manage'        => '',
            'actions'       => '',
            ),
        'list' => array
            (
            'pageTitle'     => 'Page Manager',
            'instructions'  => 'instructions_page_list',
            'manage'        => '',
            'actions'       => array
                (
                'new section' => SGL_Url::makeLink('add','page','navigation'),
                'change style' => SGL_Url::makeLink('list','navstyle','navigation'),
                ),
            ),
        'add' => array
            (
            'pageTitle'     => 'Page Manager :: Add',
            'instructions'  => '',
            'manage'        => '',
            'actions'       => '',
            ),
        'edit' => array
            (
            'pageTitle'     => 'Page Manager :: Edit',
            'instructions'  => 'instructions_page_edit',
            'manage'        => '',
            'actions'       => '',
            ),
        'update' => array
            (
            'pageTitle'     => 'Page Manager :: Edit',
            'instructions'  => 'instructions_page_edit',
            'manage'        => '',
            'actions'       => '',
            ),
        'reorder' => array
            (
            'pageTitle'     => 'Page Manager :: Reorder',
            'instructions'  => 'Pour déplacer une catégorie dans l\'arborescence, il suffit de cliquer sur la flèche correspondante (haut ou bas). Si vous désirez modifier la hiérarchie des catégories (c\'est à dire changer la catégorie parente), cliquez sur "Editer" et sélectionnez la nouvelle "Catégorie Parente".',
            'manage'        => '',
            'actions'       => '',
            ),
        ),
    'navstyle' => array
        (
        'all' => array
            (
            'pageTitle'     => 'Navigation Style Manager',
            'instructions'  => 'Cette fonction vous permet de prévisualiser le menu principal tel qu\'il sera affiché en fonction du style et du rôle sélectionnés.',
            'manage'        => '',
            'actions'       => array
                (
                'Back' => SGL_Url::makeLink('list','page','navigation'),
                ),
            ),
        'list' => array
            (
            'pageTitle'     => 'Navigation Style Manager',
            'instructions'  => 'Cette fonction vous permet de prévisualiser le menu principal tel qu\'il sera affiché en fonction du style et du rôle sélectionnés.',
            'manage'        => '',
            'actions'       => array
                (
                'Back' => SGL_Url::makeLink('list','page','navigation'),
                ),
            ),
        )
    ),
'newsletter' => array
    (
    
    ),
'publisher' => array
    (
    'article' => array
        (
        'all' => array
            (
            'pageTitle'     => 'Article Manager',
            'instructions'  => '',
            'manage'        => array
                (
                'Categories' => SGL_Url::makeLink('', 'category', 'navigation', array(), "frmCatID|{$input->category_id}"),
                'Articles' => SGL_Url::makeLink('','article','publisher'),
                'Documents' => SGL_Url::makeLink('','document','publisher'),
                ),
            'actions'       => '',
            ),
        'list' => array
            (
            'pageTitle'     => 'Article Manager',
            'instructions'  => 'instructions_article_list',
            'manage'        => '',
            'actions'       => array
                (
                'more info' => 'javascript:toggleDisplay(\'articleHelp\')'
                ),
            ),
        'add' => array
            (
            'pageTitle'     => 'Article Manager :: Add',
            'instructions'  => 'instructions_article_add',
            'manage'        => '',
            'actions'       => array
                (
                'Save' => 'javascript:articleSave()',
                'Approve' => 'javascript:articleApprove()',
                'Publish' => 'javascript:articlePublish()'
                ),
            ),
        'edit' => array
            (
            'pageTitle'     => 'Article Manager :: Edit',
            'instructions'  => 'instructions_article_edit',
            'manage'        => '',
            'actions'       => array
                (
                'Update' => 'javascript:articleUpdate()',
                'Save' => 'javascript:articleSave()',
                'Approve' => 'javascript:articleApprove()',
                'Publish' => 'javascript:articlePublish()'
                ),
            ),
        'save' => array
            (
            'pageTitle'     => 'Article Manager :: Edit',
            'instructions'  => 'instructions_article_edit',
            'manage'        => '',
            'actions'       => array
                (
                'Update' => 'javascript:articleUpdate()',
                'Save' => 'javascript:articleSave()',
                'Approve' => 'javascript:articleApprove()',
                'Publish' => 'javascript:articlePublish()'
                ),
            ),
        'view' => array
            (
            'pageTitle'     => 'Article Manager :: View',
            'instructions'  => '',
            'manage'        => '',
            'actions'       => '',
            ),
        ),
    'document' => array
        (
        'all' => array
            (
            'pageTitle'     => 'Document Manager',
            'instructions'  => '',
            'manage'        => array
                (
                'Categories' => SGL_Url::makeLink('', 'category', 'navigation', array(), "frmCatID|{$input->category_id}"),
                'Articles' => SGL_Url::makeLink('','article','publisher'),
                'Documents' => SGL_Url::makeLink('','document','publisher'),
                ),
            'actions'       => '',
            ),
        'list' => array
            (
            'pageTitle'     => 'Document Manager',
            'instructions'  => 'Parcourez la liste des documents disponibles. Vous pouvez basculer entre l\'affichage par catégorie ou pour l\'ensemble des catégories en cliquant sur le lien correspondant.<br />Pour ajouter un document cliquez sur "Nouveau document".<br />Dans la liste des documents, cliquez sur "Editer" pour modifier les informations sur un document.',
            'manage'        => '',
            'actions'       => array
                (
                'New Asset' => SGL_Url::makeLink('add','document','publisher'),
                ),
            ),
        'add' => array
            (
            'pageTitle'     => 'Document Manager :: Add',
            'instructions'  => 'Pour ajouter un nouveau document il vous suffit de sélectionner la catégorie dans laquelle vous désirez le rendre disponible, puis de cliquez sur "Parcourir" pour sélectionner le document sur votre disque dur.<br />Une fois le fichier importé dans votre site, vous pourrez fournir des informations telles le nom et la description de ce fichier.<br />Les formats courants de documents sont supportés (.doc, .xls, .ppt, .pdf, ...). Si vous souhaitez disposer de formats supplémentaires, contactez votre administrateur préféré.',
            'manage'        => '',
            'actions'       => '',
            ),
        'edit' => array
            (
            'pageTitle'     => 'Document Manager :: Edit',
            'instructions'  => 'Parcourez la liste des documents disponibles. Vous pouvez basculer entre l\'affichage par catégorie ou pour l\'ensemble des catégories en cliquant sur le lien correspondant.<br />Pour ajouter un document cliquez sur "Nouveau document".<br />Dans la liste des documents, cliquez sur "Editer" pour modifier les informations sur un document.',
            'manage'        => '',
            'actions'       => '',
            ),
        'view' => array
            (
            'pageTitle'     => 'Document Manager :: View',
            'instructions'  => '',
            'manage'        => '',
            'actions'       => '',
            ),
        ),
    ),
'randommsg' => array
    (
    
    ),
'user' => array
    (
    'user' => array
        (
        'all' => array
            (
            'pageTitle'     => 'User Manager',
            'instructions'  => '',
            'manage'        => array
                (
                'users' => SGL_Url::makeLink('list','user','user'),
                'roles' => SGL_Url::makeLink('list','role','user'),
                'perms' => SGL_Url::makeLink('list','permission','user'),
                'prefs' => SGL_Url::makeLink('list','preference','user'),
                ),
            'actions'       => '',
            ),
        'list' => array
            (
            'pageTitle'     => 'User Manager :: Browse',
            'instructions'  => 'Ce module vous permet de gérer les utilisateurs de votre système.<br />Vous pouvez en ajouter de nouveaux, modifier leurs infos. Pour modifier les permissions rattachées à chaque utilisateur, cliquez sur "changer". Si les permissions à modifier concernent un groupe d\'utilisateurs, modifiez plutôt les permissions rattachées au rôle concerné, puis faîtes une synchronisation des utilisateurs correspondants.',
            'manage'        => '',
            'actions'       => array
                (
                'add user' => SGL_Url::makeLink('add','user',''),
                'search' => SGL_Url::makeLink('add','usersearch','user'),
                'import users' => SGL_Url::makeLink('','userimport',''),
                ),
            ),
        'add' => array
            (
            'pageTitle'     => 'User Manager :: Add',
            'instructions'  => 'Utilisez ce formulaire pour ajouter un nouvel utilisateur. N\'oubliez pas de cocher la case "actif". Vous pouvez également modifier le statut "Actif/Inactif" d\'un utilisateur depuis la <a href="'.SGL_Url::makeLink('','user').'">liste des utilisateurs du système</a>.',
            'manage'        => '',
            'actions'       => '',
            ),
        'edit' => array
            (
            'pageTitle'     => 'User Manager :: Edit',
            'instructions'  => 'Utilisez ce formulaire pour modifier les informations concernant un utilisateur. N\'oubliez pas de cocher la case "actif". Vous pouvez également modifier le statut "Actif/Inactif" d\'un utilisateur depuis la <a href="'.SGL_Url::makeLink('','user').'">liste des utilisateurs du système</a>.',
            'manage'        => '',
            'actions'       => '',
            ),
        'viewLogin' => array
            (
            'pageTitle'     => 'User Manager :: Login Data',
            'instructions'  => 'Cette vue vous permet de consulter toutes les connexions au site d\'un utilisateur particulier.<br />Vous pouvez modifier le nombre de résultats affichés par page.',
            'manage'        => '',
            'actions'       => '',
            ),
        'requestPasswordReset' => array
            (
            'pageTitle'     => 'User Manager :: Reset password',
            'instructions'  => 'instructions_requestPasswordReset',
            'manage'        => '',
            'actions'       => '',
            ),
        'requestChangeUserStatus' => array
            (
            'pageTitle'     => 'User Manager :: Change status',
            'instructions'  => 'Cette fonction vous permet d\'activer/désactiver un utilisateur.',
            'manage'        => '',
            'actions'       => '',
            ),
        'editPerms' => array
            (
            'pageTitle'     => 'User Manager :: Edit permissions',
            'instructions'  => 'Ajouter ou supprimer des permissions pour cet utilisateur.',
            'manage'        => '',
            'actions'       => '',
            ),
        ),
    'userimport' => array
        (
        'all' => array
            (
            'pageTitle'     => 'User Import Manager',
            'instructions'  => 'Ce module vous permet d\'importer des utilisateurs depuis un fichier ".csv".',
            'manage'        => array
                (
                'users' => SGL_Url::makeLink('list','user','user'),
                'roles' => SGL_Url::makeLink('list','role','user'),
                'perms' => SGL_Url::makeLink('list','permission','user'),
                'prefs' => SGL_Url::makeLink('list','preference','user'),
                ),
            'actions'       => '',
            ),
        'list' => array
            (
            'pageTitle'     => 'User Import Manager',
            'instructions'  => 'Ce module vous permet d\'importer des utilisateurs depuis un fichier ".csv".',
            'manage'        => '',
            'actions'       => '',
            ),
        ),
    'usersearch' => array
        (
        'all' => array
            (
            'pageTitle'     => 'User Manager :: Search',
            'instructions'  => 'Cette vue vous permet de rechercher des utilisateurs en remplissant les critères dans le formulaire suivant.',
            'manage'        => array
                (
                'users' => SGL_Url::makeLink('list','user','user'),
                'roles' => SGL_Url::makeLink('list','role','user'),
                'perms' => SGL_Url::makeLink('list','permission','user'),
                'prefs' => SGL_Url::makeLink('list','preference','user'),
                ),
            'actions'       => array
                (
                'add user' => SGL_Url::makeLink('add','user',''),
                'search' => SGL_Url::makeLink('add','usersearch','user'),
                'import users' => SGL_Url::makeLink('','userimport',''),
                ),
            ),
        ),
    'role' => array
        (
        'all' => array
            (
            'pageTitle'     => 'Role Manager',
            'instructions'  => '',
            'manage'        => array
                (
                'users' => SGL_Url::makeLink('list','user','user'),
                'roles' => SGL_Url::makeLink('list','role','user'),
                'perms' => SGL_Url::makeLink('list','permission','user'),
                'prefs' => SGL_Url::makeLink('list','preference','user'),
                ),
            'actions'       => '',
            ),
        'list' => array
            (
            'pageTitle'     => 'Role Manager :: Browse',
            'instructions'  => 'instructions_role_list',
            'manage'        => '',
            'actions'       => array
                (
                'add role' => SGL_Url::makeLink('add','role','user'),
                ),
            ),
        'add' => array
            (
            'pageTitle'     => 'Role Manager :: Add',
            'instructions'  => 'instructions_role_add',
            'manage'        => '',
            'actions'       => '',
            ),
        'edit' => array
            (
            'pageTitle'     => 'Role Manager :: Edit',
            'instructions'  => 'instructions_role_edit',
            'manage'        => '',
            'actions'       => '',
            ),
        'editPerms' => array
            (
            'pageTitle'     => 'Role Manager :: Edit permissions',
            'instructions'  => 'instructions_role_editPerms',
            'manage'        => '',
            'actions'       => '',
            ),
        ),
    'permission' => array
        (
        'all' => array
            (
            'pageTitle'     => 'Permission Manager',
            'instructions'  => '',
            'manage'        => array
                (
                'users' => SGL_Url::makeLink('list','user','user'),
                'roles' => SGL_Url::makeLink('list','role','user'),
                'perms' => SGL_Url::makeLink('list','permission','user'),
                'prefs' => SGL_Url::makeLink('list','preference','user'),
                ),
            'actions'       => '',
            ),
        'list' => array
            (
            'pageTitle'     => 'Permission Manager :: Browse',
            'instructions'  => 'instructions_permission_list',
            'manage'        => '',
            'actions'       => array
                (
                'add permission' => SGL_Url::makeLink('add','permission','user',array(),"frmPermId|{$input->moduleId}"),
                'detect & add permissions' => SGL_Url::makeLink('scanNew','permission','user',array(),"frmPermId|{$input->moduleId}"),
                'remove orphaned' => SGL_Url::makeLink('scanOrphaned','permission','user',array(),"frmPermId|{$input->moduleId}"),
                ),
            ),
        'add' => array
            (
            'pageTitle'     => 'Permission Manager :: Add',
            'instructions'  => 'instructions_permission_add',
            'manage'        => '',
            'actions'       => '',
            ),
        'edit' => array
            (
            'pageTitle'     => 'Permission Manager :: Edit',
            'instructions'  => 'instructions_permission_edit',
            'manage'        => '',
            'actions'       => '',
            ),
        'scanNew' => array
            (
            'pageTitle'     => 'Permission Manager :: Detect & Add',
            'instructions'  => 'instructions_permission_scanNew',
            'manage'        => '',
            'actions'       => '',
            ),
        'scanOrphaned' => array
            (
            'pageTitle'     => 'Permission Manager :: Detect Orphaned',
            'instructions'  => 'instructions_permission_scanOrphaned',
            'manage'        => '',
            'actions'       => '',
            ),
        ),
    'preference' => array
        (
        'all' => array
            (
            'pageTitle'     => 'Preference Manager',
            'instructions'  => '',
            'manage'        => array
                (
                'users' => SGL_Url::makeLink('list','user','user'),
                'roles' => SGL_Url::makeLink('list','role','user'),
                'perms' => SGL_Url::makeLink('list','permission','user'),
                'prefs' => SGL_Url::makeLink('list','preference','user'),
                ),
            'actions'       => '',
            ),
        'list' => array
            (
            'pageTitle'     => 'Preference Manager :: Browse',
            'instructions'  => 'instructions_preference_list',
            'manage'        => '',
            'actions'       => array
                (
                'add preference' => SGL_Url::makeLink('add','preference','user'),
                ),
            ),
        'add' => array
            (
            'pageTitle'     => 'Preference Manager :: Add',
            'instructions'  => 'instructions_preference_add',
            'manage'        => '',
            'actions'       => '',
            ),
        'edit' => array
            (
            'pageTitle'     => 'Preference Manager :: Edit',
            'instructions'  => 'instructions_preference_edit',
            'manage'        => '',
            'actions'       => '',
            ),
        ),
    ),
);
//echo'$managerOptions<br /><pre>';die(print_r($managerOptions));
?>