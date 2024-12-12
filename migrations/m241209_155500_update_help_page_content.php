<?php

use yii\db\Migration;

/**
 * Class m241209_155500_update_help_page_content
 */
class m241209_155500_update_help_page_content extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Define the content to be updated
        $content = <<<HTML
    <link href="/css/site/under_construction.css" rel="stylesheet">
    <div class='title row'>
        <div class="col-md-offset-11 col-md-1 float-right">
            <a class="btn btn-default" href="index.php?r=project%2Findex"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>
    <div class="row" id="back">&nbsp;</div><br><br>
    <h1 style="color: rgb(230, 131, 59);">How to connect</h1>
    <a href="#hypatia-use">Who can use HYPATIA?</a><br>
    <a href="#aai-what">What is the LS AAI?</a><br>
    <a href="#aai-account">How do I create a LS AAI account?</a><br>
    <a href="#aai-login">How do I login to HYPATIA using my newly created LS AAI account?</a><br>
    <a href="#data">What data do you keep in the HYPATIA database regarding my LS AAI Account?</a><br>
    <a href="#passw">What happens if I forget my password?</a><br>
    <h1 style="color: rgb(230, 131, 59);">HYPATIA</h1>
    <a href="#users">What are the available user types in HYPATIA?</a><br>
    <a href="#project-types">What project types exist in HYPATIA?</a><br>
    <a href="#new-proj">How do I create a new project?</a><br>
    <a href="#24/7">How do I create a new “24/7 services” project?</a><br>
    <a href="#ondemand">How do I create a new “On-demand batch computations” project?</a><br>
    <a href="#machine">How do I create a new “On-demand computation machines” project (available only to gold users)?</a><br>
    <a href="#notebook">How do I create a new “On-demand notebook” project?</a><br>
    <a href="#storage">How do I create a new “Storage volume” project?</a><br>
    <a href="#request">What happens after I submit a project request?</a><br>
    <a href="#notif">Why did I get a notification saying my project was automatically accepted?</a><br>
    <a href="#expire">What happens when my project is close to the expiration date?</a><br>
    <a href="#email">How do I get notifications by e-mail instead of only on the interface?</a><br>
    <a href="#list">Is there a list of all my previous requests?</a><br>
    <a href="#update">How do I update my projects?</a><br>
    <a href="#forget">I forgot to update my project expiration date and it expired. What can I do?</a><br>
    <a href="#details">How do I view details about my projects?</a><br>
    <a href="#access">My project was approved. How do I access the resources?</a><br>
    <a href="#vm">How do I create a VM (24/7 services, on-demand computation machines)?</a><br>
    <a href="#connection">What are the connection details for my VM?</a><br>
    <a href="#start">Can I stop/start or reboot my VM?</a><br>
    <a href="#console">How do I access my VM via the default console?</a><br>
    <a href="#delete">How do I delete a VM?</a><br>
    <a href="#activate">How do I activate the copy of Windows Server 2019 on my VM?</a><br>
    <a href="#multiple">How do I create multiple VMs (On-demand compute machines)?</a><br>
    <a href="#volcreatedel">How can I create and delete a volume?</a><br>
    <a href="#manage">How do I manage volume attachments on my VM?</a><br>
    <a href="#volresize">How do I resize my volume?</a><br>
    <a href="#feedback">How do I submit a bug, proposed feature or feedback in general?</a><br>
    <a href="/hypatias-policy.pdf" target="_blank" title="View Policy Document">HYPATIA’s Resource Management & Access Policy</a><br>
    <div class="row">&nbsp;</div>
    <!--<h1>HYPATIA’s Resource Management & Access Policy</h1>-->
    
    <h1>How to connect</h1>
    <div id="hypatia-use"></div><br><br>
    <h2>Who can use HYPATIA?</h2>
    Anyone having an active LS AAI account can login to HYPATIA. For details about what an LS AAI account is and how to get one, see below.
    <br><a href="#back">Back to top </a><br>
    <div id="aai-what"></div><br><br><br>
    <h2 id="aai-what">What is the LS AAI?</h2>
    The LS Authentication and Authorisation Infrastructure (AAI) is a Single Sign-On (SSO) service that enables researchers to use their home organization credentials or community or commercial identities (e.g. ORCID, LinkedIn) to sign in and access data and services they need. It also allows service providers (both in academia and industry) to control and manage the access rights of their users and create different access levels for research groups or international projects. This facilitates easy access to LS services and resources for researchers, by removing the need to remember different passwords for each service and instead using their institutional credentials to sign in via their home institution.
    <br><a href="#back">Back to top </a><br>
    
    <div id="aai-account"></div><br><br><br>
    <h2>How do I create a LS AAI account?</h2>
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/about1.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    By pressing the “Login” button on the upper right-hand corner of the HYPATIA platform the following screen will be loaded:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/register2.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    You will need press the register button, in order to be transferred to the LS AAI login page, where you will need to select your preferred method of login (Institutional account, LinkedIn, Apple, Google, ORCID, GitHub or LifeScience Hostel):
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/3elixir.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    If your institution does not appear in the list of available institutions, then you need to add it to the system by clicking on the “Cannot find your institution” button:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/4search.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    After selecting the preferred method of login you will be transferred to the respective interface to login. Then you need to follow the instructions from the LS AAI interface to create a new account with a username of your choice.
    <br><a href="#back">Back to top </a><br>
    
    <div id="aai-login"></div><br><br><br>
    <h2>How do I login to HYPATIA using my newly created LS AAI account?</h2>
    <div class="row">&nbsp;</div>
    You need to follow the same procedure as registering, but instead of pressing the “REGISTER”, you press the “LOGIN” button:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/login5.png" width="70%"">
    </div>
    <div class="row">&nbsp;</div>
    
    Then you need to select your preferred login method, as before, and then you are redirected to the hypatia website where you have been logged-in using your LS AAI username:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/active.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    <br><a href="#back">Back to top </a><br>
    
    <div id="data"></div><br><br><br>
    <h2> What data do you keep in the HYPATIA database regarding my LS AAI Account?</h2>
    
    In accordance with our Privacy policy, the only information stored in our database is your LS AAI username that you selected when you created your account, which is necessary for authentication purposes required by the HYPATIA website. The data are not shared with any third party and you reserve your right to ask for their deletion at any time, in accordance with the GDPR. No other sensitive information regarding your e-mail, institution or other personal information is retrieved from the service.
    <br><a href="#back">Back to top </a><br>
    
    <div id="passw"></div><br><br><br>
    <h2>What happens if I forget my password?</h2>
    
    Since we use a Single Sign-On service (SSO) your password is not stored by HYPATIA, but rather by your institution or the service that you use to login to the LS AAI, you need to follow the appropriate procedures of the respective organization to reset your password.
    <br><a href="#back">Back to top </a><br><br><br>
    
    
    <h1>HYPATIA</h1>
    
    <div id="users"></div><br><br><br>
    <h2>What are the available user types in HYPATIA?</h2>
    
    Currently, based on their position of each individual in their organisation, HYPATIA users will be classified in one of the following user types:
    <ul>
        <li>Bronze: This type of user is the one with the less privileges and the most limitations. It is assigned as the default type upon registration. It can be used for testing or educational purposes during training events (indicatively). </li>
        <li>Silver user: This type of user is suitable for regular lab members. </li>
        <li>Gold user: This type of user is for Principal Investigators and it comes with extended quotas and permissions.
            <br><br>In order to upgrade your account from bronze to silver or gold, click <a href="index.php?r=ticket-user%2Fopen&upgrade=1">here</a> to create a new ticket request, stating your position and your team’s Principal Investigator.  </li>
    </ul>
    <br><a href="#back">Back to top </a><br>
    
    <div id="project-types"></div><br><br><br>
    <h2>What project types exist in HYPATIA?</h2>
    <ul>
        <li>“24/7 services” projects. This type of project is suitable for hosting 24/7 services, following the Virtual Private Server (VPS) model. The focus is on relatively lightweight services (e.g., Web server, API endpoint, database); computationally intensive projects should be served by “on-demand batch computations” or “on-demand computation machine” projects (see below). </li>
        <li>On-demand batch computations” projects. This type of project is suitable for batches of computational tasks to be executed. Each computational task could involve the execution of a particular software product or of a workflow that combines many software products. </li>
        <li>“On-demand computation machines” projects. This type of project is suitable for (short-term) computational experiments that are not containerised. A VM having particular characteristics is provided to the user. It is available only for gold users. </li>
        <li>“On-demand notebook” projects. Provide customizable, pre-configured Jupyter environments tailored for interactive data analysis in fields like data science and machine learning, with flexible core and memory allocations, limited project durations, and extension options based on resource availability. </li>
        <li>“Storage volumes” projects. This type of project is suitable for creating storage volumes that can be attached to VMs that belong to “24/7 services” or “On-demand computation machines” projects. Storage volumes for “On-demand computation machines” projects are only available to gold users.</li>
    </ul>
    <br><a href="#back">Back to top </a><br>
    
    <div id="new-proj"></div><br><br><br>
    <h2>How do I create a new project?</h2>
    Press the “New project on the top right corner:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/newproject.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    
    The system will load a new page where you can select the project type you need:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/projects.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    <br><a href="#back">Back to top </a><br>
    
    <div id="24/7"></div><br><br><br>
    <h2>How do I create a new “24/7 services” project?</h2>
    Press on the “24/7 service button” on the new project page. The following screen will appear:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/247.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    
    You then need to fill in the required information and click on the submit button:
    <ul>
        <li> Name: The name of the project .</li>
        <li> End date: Use the pop-up calendar to provide an ending date for the project.</li>
        <li>Maximum number of participating users: Select the maximum number of users, who need to access the project’s resources.</li>
        <li>Participating users: Enter the usernames of the users you would like to add as participants and select the appropriate value from the auto-complete box.</li>
        <li>Technology Readiness Level (TRL): read more here: https://en.wikipedia.org/wiki/Technology_readiness_level.</li>
        <li>Name of the 24/7 service: name of an existing or proposed service, to which the requested resources will be allocated.</li>
        <li>Version of the 24/7 service: version of the service.</li>
        <li>Description of the 24/7 service: Write a short description regarding the existing/proposed service.</li>
        <li>Existing service URL: A URL to the existing service (if applicable).</li>
        <li>VM configuration: Select one of the available configurations for the VM you would like to create. (IMPORTANT: VMs with Solid State Disk (SSD) use ephemeral storage and therefore, we cannot guarantee the safety of the data).</li>
    </ul>
    <br><a href="#back">Back to top </a><br>
    
    <div id="ondemand"></div><br><br><br>
    <h2>How do I create a new “On-demand batch computations” project?</h2>
    Press on the “On-demand batch computations” project on the new project page. The following screen will appear:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/ondemand.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    You then need to fill in the required information and click on the submit button:
    <ul>
        <li>  Name: The name of the project
        <li>    End date: Use the pop-up calendar to provide an ending date for the project
        <li>    Maximum number of participating users: Select the maximum number of users, who need to access the project’s resources.
        <li>    Participating users: Enter the usernames of the users you would like to add as participants and select the appropriate value from the auto-complete box.
        <li>    Type of analysis: The type of analysis you would like to perform using containerized software.
        <li>    Maturity: Select the appropriate maturity level of the analysis
        <li>  Description: Write a short description of what the analysis does.
        <li>    Maximum number of jobs: The maximum number of jobs that will be run on HYPATIA-COMPUTE.
        <li>    Available cores per job: The maximum number of virtual cores that will be allocated per job.
        <li>    Maximum allowed memory: The maximum number of Random Access Memory (RAM) that will be allocated per job.
    </ul>
    <br><a href="#back">Back to top </a><br>
    
    <div id="machine"></div><br><br><br>
    <h2>How do I create a new “On-demand computation machines” project (available only to gold users)?</h2>
    Press on the “On-demand computation machines” project on the new project page. The following screen will appear:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/machine.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    You then need to fill in the required information and click on the submit button:
    <ul>
        <li>    Name: The name of the project. </li>
        <li>    End date: Use the pop-up calendar to provide an ending date for the project. Keep in mind that you cannot select a date that is later than 30 days.</li>
        <li>    Maximum number of participating users: Select the maximum number of users, who need to access the project’s resources.</li>
        <li>    Participating users: Enter the usernames of the users you would like to add as participants and select the appropriate value from the auto-complete box.</li>
        <li>    Analysis description: A short description of the analysis that will be performed on the VM.</li>
        <li>    VM configuration: select one of the available configurations for the VM you would like to create. (IMPORTANT: VMs with Solid State Disk (SSD) use ephemeral storage and therefore, we cannot guarantee the safety of the data).</li>
        <li>    Number of VMs: When a number of smaller VMs are required, then you can select up to 30 VMs.</li>
    </ul>
    <br><a href="#back">Back to top </a><br>
    
    <div id="notebook"></div><br><br><br>
    <h2>How do I create a new “On-demand notebook” project?</h2>
    Press on the “On-demand notebook” project on the new project page. The following screen will appear:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/notebook.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    You then need to fill in the required information and click on the submit button:
    <ul>
        <li>    Name: The name of the project. </li>
        <li>    End date: Use the pop-up calendar to provide an ending date for the project. Keep in mind that you cannot select a date that is later than 30 days.</li>
        <li>    Maximum number of participating users: Select the maximum number of users, who need to access the project’s resources.</li>
        <li>    Participating users: Enter the usernames of the users you would like to add as participants and select the appropriate value from the auto-complete box.</li>
        <li>    Description: A short description.</li>
        <li>    Jupyter server type: The type of Jupyter server you would like to perform.</li>
        <li>    Available cores: The maximum number of cores that will be allocated per server.
        <li>    Maximum allowed memory: The maximum number of Random Access Memory (RAM) that will be allocated per server.
    </ul>
    <br><a href="#back">Back to top </a><br>
    
    <div id="storage"></div><br><br><br>
    <h2>How do I create a new “Storage volume” project?</h2>
    Press on the “Storage volumes” project on the new project page. The following screen will appear:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/storage.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    You then need to fill in the required information and click on the submit button:
    <ul>
        <li>    Name: The name of the project. </li>
        <li>    Maximum number of participating users: Select the maximum number of users, who need to access the project’s resources.</li>
        <li>    Participating users: Enter the usernames of the users you would like to add as participants and select the appropriate value from the auto-complete box.</li>
        <li>    Description: A short description.</li>
        <li>    Volume type: At the moment, only “hot storage” is supported.</li>
        <li>    VM type: Storage projects can be created for 24/7 service VMs as well as on-demand computation machines VMs (available only to gold users). Note that, after submission the VM type cannot be changed.</li>
    </ul>
    After the acceptance of the project, you will need to create the volume from HYPATIA’s interfacea volume is created automatically. Keep in mind that a volume can be attached only to one VM at a time and multi-attach functionality is not supported.
    <br><a href="#back">Back to top </a><br>
    
    <div id="request"></div><br><br><br>
    <h2>What happens after I submit a project request?</h2>
    After you submit a project request, it is reviewed according to the access policy of HYPATIA. Once a decision is reached you will get notified on the website. If you would like to receive notifications via e-mail, then enter your e-mail address in the system (see question: “How do I get notifications by e-mail instead of only on the interface”).
    <br><a href="#back">Back to top </a><br>
    
    <div id="notif"></div><br><br><br>
    <h2> Why did I get a notification saying my project was automatically accepted?</h2>
    Each user has a limited number of Automatically Accepted Projects (AAPs), i.e., projects that can be accepted without review: bronze users may create up to 1 AAP, silver users up to 3 AAPs, gold users up to 3 AAPs. On-demand compute machines projects are not automatically accepted.
    <br><a href="#back">Back to top </a><br>
    
    <div id="expire"></div><br><br><br>
    <h2>What happens when my project is close to the expiration date?</h2>
    You will get notifications on the interface 30, 15, 5 and 1 day before expiration. If you would like to receive notifications via e-mail, then enter your e-mail address in the system.
    <br><a href="#back">Back to top </a><br>
    
    <div id="email"></div><br><br><br>
    <h2> How do I get notifications by e-mail instead of only on the interface?</h2>
    You need to select “User options” from the top menu and then “Email notifications”. You will see the following page:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/email.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    You will need to enter the e-mail address on which you would like to receive notifications and also select which notifications you would like to receive and finally press submit. These settings can be modified at any time.
    <br><a href="#back">Back to top </a><br>
    
    <div id="list"></div><br><br><br>
    <h2>Is there a list of all my previous requests?</h2>
    On your dashboard, press the “Project requests” button:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/userprojects.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    <br><a href="#back">Back to top </a><br>
    
    <div id="update"></div><br><br><br>
    <h2>How do I update my projects?</h2>
    You press the “Update” button for the project on your dashboard:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/update.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    <br><a href="#back">Back to top </a><br>
    
    <div id="forget"></div><br><br><br>
    <h2>I forgot to update my project expiration date and it expired. What can I do?</h2>
    Please contact an administrator and provide the name of your project.
    <br><a href="#back">Back to top </a><br>
    
    <div id="details"></div><br><br><br>
    <h2>How do I view details about my projects?</h2>
    You press the “Details” button for the project on your dashboard:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/details.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    <br><a href="#back">Back to top </a><br>
    
    <div id="access"></div><br><br><br>
    <h2>My project was approved. How do I access the resources?</h2>
    You press the “Access” button for the project on your dashboard:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/access.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    <br><a href="#back">Back to top </a><br>
    
    <div id="vm"></div><br><br><br>
    <h2>How do I create a VM (24/7 services, on-demand computation machines)?</h2>
    You press the “Access” button for the project and then you are transferred to the following page:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/vmcreate.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    You need to upload a file containing a public SSH key, select the Operating System (OS) image you would like to use from the list and press submit. The creation process
    may take up to several minutes to complete (especially for windows machines). After the VM is created, you can see its details on the “VM details page” (see below).
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/vmdetails.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    <br><a href="#back">Back to top </a><br>
    
    <div id="connection"></div><br><br><br>
    <h2> What are the connection details for my VM? </h2>
    You can view the IP address for the VM, as well as the default username with which the VM can be accessed. Also, you can see the storage that has been attached to the VM and you can shut down, start, reboot and access your VM via Console. Finally, by pressing the delete button you can delete your VM.
    For VMs using the Windows server OS, you will see a button titled “Retrieve password” where you can retrieve the password for the “Administrator” user. You need to save it because it cannot be retrieved again.
    <br><a href="#back">Back to top </a><br>
    
    <div id="start"></div><br><br><br>
    <h2>Can I stop/start or reboot my VM?</h2>
    Yes, you can, by pressing the appropriate buttons on the VM details page.
    <br><a href="#back">Back to top </a><br>
    
    <div id="console"></div><br><br><br>
    <h2>How do I access my VM via the default console?</h2>
    Press the appropriate button on the VM details page.
    <br><a href="#back">Back to top </a><br>
    
    <div id="delete"></div><br><br><br>
    <h2>How do I delete a VM?</h2>
    Press the appropriate button on the VM details page.
    <br><a href="#back">Back to top </a><br>
    
    <div id="activate"></div><br><br><br>
    <h2>How do I activate the copy of Windows Server 2019 on my VM?</h2>
    You need to acquire a valid Windows Server 2019 Standard Edition licence key. Then you need to open a console on the VM and enter the following command:<br><br>
    
    <i>DISM /online /Set-Edition:ServerStandard /ProductKey:XXXXX-XXXXX-XXXXX-XXXXX-XXXXX /AcceptEula</i><br><br>
    
    Where XXX is your key. After that, you need to execute:<br><br>
    
    <i>slmgr /xpr </i><br><br>
    
    to check that the OS has been activated correctly.
    
    <br><a href="#back">Back to top </a><br>
    
    <div id="multiple"></div><br><br><br>
    <h2>How do I create multiple VMs (On-demand compute machines)?</h2>
    Press the access button and the following page (where you can create and administer each VM will load:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/multiplevm.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    <br><a href="#back">Back to top </a><br>
    
    <div id="volcreatedel"></div><br><br><br>
    <h2>How do I create and delete a volume?</h2>
    Press the “Access” button for the project and the following page will load:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/volcreatedel.png" width="70%">
    </div>
    
    <div id="manage"></div><br><br><br>
    <h2>How do I manage volume attachments on my VM?</h2>
    Press the “Access” button for the project and the following page will load:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/volumes.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    
    You can see the name of the volume, the creation date, and which machine to which it is attached. Moreover, by pressing the “Manage Attachment” button, the following page opens:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/manage.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    where you can select a VM from the drop-down list to which to attach the volume.
    If the volume is already attached, you will see the following page:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/detach.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    where you can choose to detach the volume from the VM.
    <br><a href="#back">Back to top </a><br>
    
    <div id="volresize"></div><br><br><br>
    <h2>How do I resize my volume?</h2>
    You need to edit the appropriate volume storage project and enter the new value. Then you need to delete and re-create your volume.
    <div class="row">&nbsp;</div>
    <br><a href="#back">Back to top </a><br>
    
    <div id="feedback"></div><br><br><br>
    <h2>How do I submit a bug, proposed feature or feedback in general?</h2>
    You can find the bug/suggestion box at the right side of the page:
    <div class="row">&nbsp;</div>
    <div class="text-center">
        <img src="/img/faq/feedback.png" width="70%">
    </div>
    <div class="row">&nbsp;</div>
    <br><a href="#back">Back to top </a><br>
    HTML;

        // Update the content where the title is "Help page"
        $this->update('pages', ['content' => $content], ['title' => 'Help page']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('pages', [
            'content' => '<h1>Original Help Page Content</h1><p>This is the original content of the Help page.</p>'
        ], ['title' => 'Help page']);
    }

}
