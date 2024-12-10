<<<HTML
<div class="row">&nbsp;</div>
<div class="row">
    <div class='col-md-12'>
        <h2>About Hypatia</h2>
        HYPATIA is the Cloud infrastructure that has been developed to support the computational needs of the  <a href="https://elixir-greece.org/" target="_blank">ELIXIR-GR community</a>, but also the broader community of life scientists in Greece and abroad. It currently hosts important ELIXIR-GR services and resources (e.g., the <a href="https://covid19dataportal.gr/" target="_blank"> national COVID19 Data Portal of Greece</a>), while it undertakes computational tasks in the context of various projects of ELIXIR-GR members.
        <br /><br />

        The infrastructure is named after <a href="https://en.wikipedia.org/wiki/Hypatia" target="_blank"> Hypatia (Υπατία)</a>, a Greek philosopher, astronomer, and mathematician, who lived in Alexandria, Egypt (born circa 350-370AD; died in 415AD).

        <br /><br />

        Under the hood, HYPATIA consists of a powerful computational cluster of heterogeneous physical machines. Currently, the HYPATIA’s cluster is comprised of:<br>
        <ul class="square-list">
            <li><strong>32 basic nodes:</strong> (2 CPUs, 14 cores/CPU, 512GB DDR4 RAM).</li>
            <li><strong>2 hefty nodes:</strong> (2 CPUs, 24 cores/CPU, 1TB DDR4 RAM)</li>
            <li><strong>3 GPU nodes:</strong> (2 CPUs, 14 cores/CPU, 768GB DDR4 RAM, 2 GPUs)</li>
            <li><strong>8 I/O nodes:</strong> (2 CPUs, 14 cores/CPU, 512GB DDR4 RAM, 2x2TB SSD 6G)</li>
            <li><strong>9 infrastructure nodes:</strong>(2 CPUs, 14 cores/CPU, 192GB DDR4 RAM)</li>
        </ul>
    </div>
</div>
<div class="row">
    <div class="col-md-12 text-center"><img class="index-img" src="/img/site/about.png" alt="" width="70%"></div>
</div>
<div class="row">&nbsp;</div>
<div class="row">

    <div class='col-md-12'>
        HYPATIA has been funded by the “ELIXIR-GR: Managing and Analysing LS Data (MIS: 5002780)” project (co-funded by Greece and the European Union - European Regional Development Fund) and it is currently hosted by a <a href="https://grnet.gr/en/" target="_blank">GRNET’s</a> data center, located in Athens, Greece.
    </div>
</div>
<div class="row">&nbsp;</div>
<div class="row">

    <div class='col-md-12'>
        <h2>How can I use the HYPATIA?</h2>
        HYPATIA’s computational resources are allocated for predetermined time periods to particular user-created projects. HYPATIA’s users are able to submit                  project requests through a dedicated Web interface, which is based on <a href="https://github.com/athenarc/clima" target="_blank">CLIMA</a>, an open source platform for the allocation of resources on Clouds. Project requests are examined by a dedicated committee that decides about their acceptance, modification, or rejection.
    </div>
</div>
<div class="row">&nbsp;</div>
<div class="row">
    <div class="col-md-12 text-center"><img class="index-img" src="/img/site/use.png" alt="" width="70%"></div>
</div>

<div class="row">&nbsp;</div>
<div class="row">
    <div class="col-md-12">
        Authentication in HYPATIA is done leveraging LS AAI technologies. This means that anyone with an active <a href="https://elixir-europe.org/services/compute/aai" target="_blank">LS AAI</a> account is able to connect. The following user types are currently provided by HYPATIA: <br>
        <ul class="square-list">
            <li><strong>Bronze user</strong>: The default type of user, having the less privileges and most limitations. This type is given for testing or educational purposes during training events. </li>
            <li><strong>Silver user</strong>: Suitable type of user for regular lab members (upgrade upon request).</li>
            <li><strong>Gold user</strong>: Suitable type of user for ELIXIR-GR’s Principal Investigators (upgrade upon request). </li>
        </ul>

        <br /> <br />
        Currently, users are able to make requests for the following types of projects:<br>
        <ul class="square-list">
            <li><strong>“24/7 services” projects</strong>: Offer VMs to host 24/7 services with relatively lightweight demands (e.g., Web servers, API endpoints, databases).</li>
            <li><strong>“On-demand batch computations” projects</strong>: Offer resources to be allocated for batches of computational tasks.</li>
            <li><strong>“On-demand computation machines” projects</strong>: Offer VMs to undertake short-term, computational-intensive experiments. </li>
            <li><strong>“On-demand notebook” projects<strong>: Provide customizable, pre-configured Jupyter environments tailored for interactive data analysis in fields like data science and machine learning, with flexible core and memory allocations, limited project durations, and extension options based on resource availability. </li>
            <li><strong>“Storage volumes” projects</strong>: Offer storage volumes that can be attached to VMs that belong to “24/7 services” or “On-demand computation machines” projects. Plans to provide cold-storage in the future.</li>
        </ul>
    </div>
</div>

<div class="row">&nbsp;</div>

<div class="row">
    <div class='col-md-12'>
        <h2>How can I sign-in to the resource management system?</h2>
        <ul class="square-list">
            <li>To sign-in to the resource management system an LS AAI account is required (just click on the “Register” button <a href="https://hypatia.athenarc.gr/index.php?r=user-management%2Fauth%2Flogin">here</a>) and wait for an approval email).</li>
            <li>When your LS AAI account is ready, you can just sign-in using your credentials (click “Login” <a href="https://hypatia.athenarc.gr/index.php?r=user-management%2Fauth%2Flogin">here</a>).</li>
            <li>During the previous steps you have to give your consent that LS AAI and HYPATIA’s resource management system will have access to basic account information required for the flawless operation of the system.</li>
        </ul>
        Learn <a href="https://elixir-europe.org/services/compute/aai" target="_blank">more</a> about the LS AAI.
    </div>
</div>
<div class="row">
    <div class="col-md-12 text-center"><img class="index-img" src="/img/site/index-unsplash-3.jpg" alt="" width="70%"></div>
</div>
</div>
HTML;