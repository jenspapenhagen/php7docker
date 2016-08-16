<?php
declare(strict_types=1);
/**
* Docker Remote API v1.24 Constructor
*
*
* @param $host string IP or hostname of the remote api server
* @param $port int Port number of the Docker deamon
*/
require_once "CurlClient.php";

class DockerClient extends CurlClient {

	/**
	 * DockerClient constructor.
	 * @param string $host
	 * @param int $port
     */
	public function __construct(string $host, int $port) {
		$this->validate_IP($host);
		parent::__construct($host, $port);
	}


	/**
     * validate_id of a docker container
     * have to be in hex and 12char long
     * or is a set name of a docker container
     * if only a name is given
	 * @param string $id
	 * @return bool
     */
	public function validate_id(string $id): bool {
        //check if not a name is given instate of an id
        if( !($this->nameisfree($id)) ) {
            if( !(ctype_xdigit($id)) or strlen($id)!==12 ) {
                    return false;
                }
            return false;
		}
        return true;
	}

    /**
     * check if a docker name is free to set
     * to avoid double names of docker container
     * @param string $name
     * @return bool
     */
    public function nameisfree(string $name): bool {
        if (!preg_match ('/[a-zA-Z0-9-+_. ]/', $name)) {
            return false;
        }

        $json = json_decode($this->listdocksrunning());
        foreach($json as $key => $value) {
            if($value->Names == $name){
                return false;
            }

        }
        return true;
    }

    /**
     * check the json object for NULL and errors
     * @param $data
     * @return bool
     */
    public function badJSON($data): bool {
        if ($data === null and json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        return true;
    }

    /**
     * create a default docker container
     * more infos here: https://docs.docker.com/engine/reference/api/docker_remote_api_v1.24/
     * @param object $parameter
     */
    public function create(object $parameter){
        $output = "";
        if(!$this->badJSON($parameter)){
            die("JSON file is NULL or have a typo. Please check.");
        }
        //adding missing parameter or force change to a bool

        //Hostname - A string value containing the hostname to use for the container.
        //This must be a valid RFC 1123 hostname.
        if(!isset($parameter[0]->Hostname)) {
            $parameter[0]->Hostname = '';
        }
        //Domainname - A string value containing the domain name to use for the container.
        if(!isset($parameter[0]->Domainname)) {
            $parameter[0]->Domainname= '';
        }
        //User - A string value specifying the user inside the container.
        if(!isset($parameter[0]->User)) {
            $parameter[0]->User= '';
        }
        //AttachStdin - Boolean value, attaches to stdin.
        if(!isset($parameter[0]->AttachStdin) or !is_bool($parameter[0]->AttachStdin)) {
            $parameter[0]->AttachStdin=false;
        }
        //AttachStdout - Boolean value, attaches to stdout.
        if(!isset($parameter[0]->AttachStdout) or !is_bool($parameter[0]->AttachStdout)) {
            $parameter[0]->AttachStdout=true;
        }
        //AttachStderr - Boolean value, attaches to stderr.
        if(!isset($parameter->AttachStderr) or !is_bool($parameter[0]->AttachStderr)) {
            $parameter[0]->AttachStderr=true;
        }
        //Tty - Boolean value, Attach standard streams to a tty, including stdin if it is not closed.
        if(!isset($parameter[0]->Tty) or !is_bool($parameter[0]->Tty)) {
            $parameter[0]->Tty=false;
        }
        //OpenStdin - Boolean value, opens stdin,
        if(!isset($parameter->OpenStdin) or !is_bool($parameter[0]->OpenStdin)) {
            $parameter[0]->OpenStdin=false;
        }
        //StdinOnce - Boolean value, close stdin after the 1 attached client disconnects.
        if(!isset($parameter[0]->StdinOnce) or !is_bool($parameter[0]->StdinOnce)) {
            $parameter[0]->StdinOnce=false;
        }

        //Env - A list of environment variables in the form of ["VAR=value"[,"VAR2=value2"]].
        //do not have to be added to top
        if(!isset($parameter[0]->Env)) {
            $parameter[]->Env=  '';
        }
        //Command to run specified as a string or an array of strings.
        if(!isset($parameter[0]->Cmd)) {
            $parameter[]->Cmd=  '';
        }
        //Set the entry point for the container as a string or an array of strings.
        if(!isset($parameter[0]->Entrypoint)) {
            $parameter[]->Entrypoint=  '';
        }
        //A string specifying the image name to use for the container.
        if(!isset($parameter[0]->Image)) {
            $parameter[]->Image=  'ubuntu';
        }
        //Adds a map of labels to a container. To specify a map: {"key":"value"[,"key2":"value2"]}
        //have to be a multidimensional array
        if(!isset($parameter[0]->Labels)) {
            $parameter[]->Labels =          array(  "com.example.vendor"    =>  "Acme",
                                                    "com.example.license"   =>  "GPL",
                                                    "com.example.version"   =>  "1.0"
                                            );
        }
        //An object mapping mount point paths (strings) inside the container to empty objects.
        if(!isset($parameter[0]->Volumes)) {
            $parameter[]->Volumes= '';
        }
        //A string specifying the working directory for commands to run in.
        if(!isset($parameter[0]->WorkingDir)) {
            $parameter[]->WorkingDir= '';
        }
        //Boolean value, when true disables networking for the container
        if(!isset($parameter[0]->NetworkDisabled) or !is_bool($parameter[0]->NetworkDisabled)) {
            $parameter[]->NetworkDisabled=false;
        }
        //a hex string like "12:34:56:78:9a:bc" to give the docker container a physical address
        if(!isset($parameter[0]->MacAddress)) {
            $parameter[]->MacAddress= '';
        }
        //An object mapping ports to an empty object in the form of:
        //"ExposedPorts": { "<port>/<tcp|udp>: {}" }
        if(!isset($parameter->ExposedPorts)) {
            $parameter[]->ExposedPorts =       array("22/tcp" => "{}"
                                            );
        }
        //Signal to stop a container as a string or unsigned integer. SIGTERM by default
        if(!isset($parameter[0]->StopSignal)) {
            $parameter[]->StopSignal = 'SIGTERM' ;
        }
        if (!empty($parameter[0]->HostConfig)) {
            if(!isset($parameter->HostConfig->Binds)) {
                //Binds – A list of volume bindings for this container.
                // Each volume binding is a string in one of these forms:
                // host_path:container_path to bind-mount a host path into the container
                // host_path:container_path:ro to make the bind-mount read-only inside the container.
                // volume_name:container_path to bind-mount a volume managed by a volume plugin into the container.
                // volume_name:container_path:ro to make the bind mount read-only inside the container.


                $parameter[]->HostConfig->Binds = "[\"/tmp:/tmp\"]";
            }
        }
        //A list of links for the container. Each link entry should be in the form of container_name:alias.
        if(!isset($parameter[0]->HostConfig->Links)) {
            $parameter[]->HostConfig->Links="[\"redis3:redis\"]" ;
        }

        //memory

        //Memory limit in bytes.
        if(!isset($parameter[0]->HostConfig->Memory)){
            $parameter[]->HostConfig->Memory=0 ;
        }
        //Total memory limit (memory + swap; set -1 to enable unlimited swap.
        // You must use this with memory and make the swap value larger than memory.
        if(!isset($parameter[0]->HostConfig->MemorySwap )){
            $parameter[]->HostConfig->MemorySwap=0 ;
        }
        //Memory soft limit in bytes.
        if(!isset($parameter[0]->HostConfig->MemoryReservation)){
            $parameter[]->HostConfig->MemoryReservation=0 ;
        }
        //Kernel memory limit in bytes.
        if(!isset($parameter[0]->HostConfig->KernelMemory)){
            $parameter[]->HostConfig->KernelMemory =0 ;
        }

        //CPU

        //An integer value containing the usable percentage of the available CPUs. (Windows daemon only)
        if(!isset($parameter[0]->HostConfig->CpuPercent) and strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
            $parameter[]->HostConfig->CpuPercent=80 ;
        }
        //An integer value containing the container’s CPU Shares (ie. the relative weight vs other containers).
        if(!isset($parameter[0]->HostConfig->CpuShares)){
            $parameter[]->HostConfig->CpuShares=512 ;
        }
        //The length of a CPU period in microseconds.
        if(!isset($parameter[0]->HostConfig->CpuPeriod)){
            $parameter[]->HostConfig->CpuPeriod=100000 ;
        }
        //Microseconds of CPU time that the container can get in a CPU period.
        if(!isset($parameter[0]->HostConfig->CpuQuota)){
            $parameter[]->HostConfig->CpuQuota=50000 ;
        }
        //String value containing the cgroups CpusetCpus to use.
        if(!isset($parameter[0]->HostConfig->CpusetCpus)){
            $parameter[]->HostConfig->CpusetCpus='0,1' ;
        }
        //Memory nodes (MEMs) in which to allow execution (0-3, 0,1). Only effective on NUMA systems.
        if(!isset($parameter[0]->HostConfig->CpusetMems)){
            $parameter[]->HostConfig->CpusetMems='0,1' ;
        }

        //Maximum IO absolute rate in terms of IOps.
        if(!isset($parameter[0]->HostConfig->MaximumIOps)){
            $parameter[]->HostConfig->MaximumIOps=0 ;
        }
        //Maximum IO absolute rate in terms of bytes per second.
        if(!isset($parameter[0]->HostConfig->MaximumIOBps)){
            $parameter[]->HostConfig->MaximumIOBps=0 ;
        }
        //Block IO weight (relative weight) accepts a weight value between 10 and 1000.
        if(!isset($parameter[0]->HostConfig->BlkioWeight)){
            $parameter[]->HostConfig->BlkioWeight=300 ;
        }
        //Block IO weight (relative device weight) in the form of:
        //"BlkioWeightDevice": [{"Path": "device_path", "Weight": weight}]
        if(!isset($parameter[0]->HostConfig->BlkioWeightDevice)){
            $parameter[]->HostConfig->BlkioWeightDevice="[{}]" ;
        }
        //Limit read rate (bytes per second) from a device in the form of:
        //"BlkioDeviceReadBps": [{"Path": "device_path", "Rate": rate}],
        //for example: "BlkioDeviceReadBps": [{"Path": "/dev/sda", "Rate": "1024"}]"
        if(!isset($parameter[0]->HostConfig->BlkioDeviceReadBps)){
            $parameter[]->HostConfig->BlkioDeviceReadBps="[{}]" ;
        }
        //Limit write rate (bytes per second) to a device in the form of:
        //"BlkioDeviceWriteBps": [{"Path": "device_path", "Rate": rate}],
        //for example: "BlkioDeviceWriteBps": [{"Path": "/dev/sda", "Rate": "1024"}]"
        if(!isset($parameter[0]->HostConfig->BlkioDeviceReadIOps)){
            $parameter[]->HostConfig->BlkioDeviceReadIOps="[{}]" ;
        }
        //Limit read rate (IO per second) from a device in the form of:
        //"BlkioDeviceReadIOps": [{"Path": "device_path", "Rate": rate}],
        //for example: "BlkioDeviceReadIOps": [{"Path": "/dev/sda", "Rate": "1000"}]
        if(!isset($parameter[0]->HostConfig->BlkioDeviceWriteBps)){
            $parameter[]->HostConfig->BlkioDeviceWriteBps="[{}]" ;
        }
        //Limit write rate (IO per second) to a device in the form of:
        //"BlkioDeviceWriteIOps": [{"Path": "device_path", "Rate": rate}],
        //for example: "BlkioDeviceWriteIOps": [{"Path": "/dev/sda", "Rate": "1000"}]
        if(!isset($parameter[0]->HostConfig->BlkioDeviceWriteIOps)){
            $parameter[]->HostConfig->BlkioDeviceWriteIOps="[{}]" ;
        }
        //Tune a container’s memory swappiness behavior. Accepts an integer between 0 and 100
        if(!isset($parameter[0]->HostConfig->MemorySwappiness)){
            $parameter[]->HostConfig->MemorySwappiness=60 ;
        }
        //Boolean value, whether to disable OOM Killer for the container or not.
        if(!isset($parameter[0]->HostConfig->OomKillDisable) or !is_bool($parameter[0]->HostConfig->OomKillDisable)) {
            $parameter[]->HostConfig->OomKillDisable=false ;
        }
        //An integer value containing the score given to the container in order to tune OOM killer preferences.
        if(!isset($parameter[0]->HostConfig->OomScoreAdj)){
            $parameter[]->HostConfig->OomScoreAdj=500 ;
        }
        //Set the PID (Process) Namespace mode for the container; "container:<name|id>":
        //joins another container’s PID namespace "host": use the host’s PID namespace inside the container
        if(!isset($parameter[0]->HostConfig->PidMode)){
            $parameter[]->HostConfig->PidMode='' ;
        }
        //Tune a container’s pids limit. Set -1 for unlimited.
        if(!isset($parameter[0]->HostConfig->PidsLimit)){
            $parameter[]->HostConfig->PidsLimit= '-1' ;
        }
        // A map of exposed container ports and the host port they should map to.
        // A JSON object in the form { <port>/<protocol>: [{ "HostPort": "<port>" }] }
        // Take note that port is specified as a string and not an integer value.
        if(!isset($parameter[0]->HostConfig->PortBindings)) {
            $parameter->HostConfig->PortBindings =
                                            array(  "22/tcp"    =>  "[{ \"HostPort\": \"11022\" }]"
                                                ) ;
        }
        //Allocates a random host port for all of a container’s exposed ports. Specified as a boolean value.
        if(!isset($parameter[0]->HostConfig->PublishAllPorts) or !is_bool($parameter[0]->HostConfig->PublishAllPorts)) {
            $parameter[]->HostConfig->PublishAllPorts= false;
        }
        //Gives the container full access to the host. Specified as a boolean value.
        if(!isset($parameter[0]->HostConfig->Privileged) or !is_bool($parameter[0]->HostConfig->Privileged)) {
            $parameter[]->HostConfig->Privileged= false;
        }
        //Mount the container’s root filesystem as read only. Specified as a boolean value.
        if(!isset($parameter[0]->HostConfig->ReadonlyRootfs) or !is_bool($parameter[0]->HostConfig->ReadonlyRootfs)) {
            $parameter[]->HostConfig->ReadonlyRootfs= false;
        }
        //A list of DNS servers for the container to use.
        if(!isset($parameter[0]->HostConfig->Dns)){
            $parameter[]->HostConfig->Dns= '["8.8.8.8","208.67.222.222"]';
        }
        //A list of DNS options
        if(!isset($parameter[0]->HostConfig->DnsOptions)){
            $parameter[]->HostConfig->DnsOptions= '[""]';
        }
        //A list of DNS search domains
        if(!isset($parameter[0]->HostConfig->DnsSearch)){
            $parameter[]->HostConfig->DnsSearch= '[""]';
        }
        //A list of hostnames/IP mappings to add to the container’s /etc/hosts file.
        //Specified in the form ["hostname:IP"]
        if(!isset($parameter[0]->HostConfig->ExtraHosts)){
            $parameter[]->HostConfig->ExtraHosts= null;
        }
        //A list of volumes to inherit from another container.
        //Specified in the form <container name>[:<ro|rw>]
        if(!isset($parameter[0]->HostConfig->VolumesFrom)){
            $parameter[]->HostConfig->VolumesFrom= '["parent", "other:ro"]';
        }
        //A list of kernel capabilities to add to the container.
        if(!isset($parameter[0]->HostConfig->CapAdd)){
            $parameter[]->HostConfig->CapAdd= '["NET_ADMIN"]';
        }
        //A list of kernel capabilities to drop from the container.
        if(!isset($parameter[0]->HostConfig->CapDrop)){
            $parameter[]->HostConfig->CapDrop= '["MKNOD"]';
        }
        //A list of additional groups that the container process will run as
        if(!isset($parameter[0]->HostConfig->GroupAdd)){
            $parameter[]->HostConfig->GroupAdd= '["newgroup"]';
        }
        //The behavior to apply when the container exits.
        // The value is an object with a Name property of either "always" to always restart,
        // "unless-stopped" to restart always except when user has manually stopped the container or
        // "on-failure" to restart only when the container exit code is non-zero.
        // If on-failure is used, MaximumRetryCount controls the number of times to retry before giving up.
        // The default is not to restart. (optional) An ever increasing delay (double the previous delay,
        // starting at 100mS) is added before each restart to prevent flooding the server.
        if(!isset($parameter[0]->HostConfig->RestartPolicy)){
            $parameter[]->HostConfig->RestartPolicy = array(  "Name"    =>  "",
                                                            "MaximumRetryCount" => 0
                                                        );
        }
        //Sets the usernamespace mode for the container when usernamespace remapping option is enabled.
        // supported values are: host.
        if(!isset($parameter[0]->HostConfig->UsernsMode)){
            $parameter[]->HostConfig->UsernsMode='';
        }
        //Sets the networking mode for the container. Supported standard values are:
        // bridge, host, none, and container:<name|id>. Any other value is taken as
        // a custom network’s name to which this container should connect to.
        if(!isset($parameter[0]->HostConfig->NetworkMode )){
            $parameter[]->HostConfig->NetworkMode ='bridge';
        }
        //A list of devices to add to the container specified as a JSON object in the form
        // { "PathOnHost": "/dev/deviceName", "PathInContainer": "/dev/deviceName", "CgroupPermissions": "mrw"}
        if(!isset($parameter[0]->HostConfig->Devices )){
            $parameter[]->HostConfig->Devices ='[]';
        }
        //A list of ulimits to set in the container, specified as
        // { "Name": <name>, "Soft": <soft limit>, "Hard": <hard limit> },
        // for example: Ulimits: { "Name": "nofile", "Soft": 1024, "Hard": 2048 }
        if(!isset($parameter[0]->HostConfig->Ulimits )){
            $parameter[]->HostConfig->Ulimits ='[{}]';
        }
        //Log configuration for the container, specified as a JSON object in the form
        // { "Type": "<driver_name>", "Config": {"key1": "val1"}}.
        // Available types: json-file, syslog, journald, gelf, fluentd, awslogs, splunk,
        // etwlogs, none. json-file logging driver.
        if(!isset($parameter[0]->HostConfig->LogConfig)){
            $parameter[]->HostConfig->LogConfig = array(  "Type"    =>  "json-file",
                                                        "Config" => "{}"
                                                        );
        }
        //A list of string values to customize labels for MLS systems, such as SELinux.
        if(!isset($parameter[0]->HostConfig->SecurityOpt )){
            $parameter[]->HostConfig->SecurityOpt ='[]';
        }
        //Storage driver options per container.
        // Options can be passed in the form {"size":"120G"}
        if(!isset($parameter[0]->HostConfig->StorageOpt )){
            $parameter[]->HostConfig->StorageOpt ='{}';
        }
        //Path to cgroups under which the container’s cgroup is created. If the path is not absolute,
        //the path is considered to be relative to the cgroups path of the init process.
        //Cgroups are created if they do not already exist.
        if(!isset($parameter[0]->HostConfig->CgroupParent )){
            $parameter[]->HostConfig->CgroupParent ='';
        }
        //Driver that this container users to mount volumes.
        if(!isset($parameter[0]->HostConfig->VolumeDriver )){
            $parameter[]->HostConfig->VolumeDriver ='';
        }
        //Size of /dev/shm in bytes. The size must be greater than 0.
        //If omitted the system uses 64MB.
        if(!isset($parameter[0]->HostConfig->ShmSize )){
            $parameter[]->HostConfig->ShmSize =67108864;
        }
        //text missing in API doc
        if(!isset($parameter[0]->NetworkingConfig)) {
            $parameter[]->NetworkingConfig = array('EndpointsConfig' =>
                                                array('isolated_nw' =>
                                                    array('IPAMConfig' =>
                                                        array(  'IPv4Address' => '172.20.30.33',
                                                                'IPv6Address' => '2001:db8:abcd::3033',
                                                                'LinkLocalIPs' => array('169.254.34.68','fe80::3468')
                                                            )
                                                        ),'Links'=>array("container_1", "container_2")
                                                         ,'Aliases'=>array("server_x", "server_y")
                                                        )
            );
        }

        return $output = $this->post('/containers/create', $parameter);
    }

    /**
     * inspect a running docker container
     * give back a json with lot of information
     * @param string $id
     * @return mixed|string
     */
    public function inspect(string $id) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)){
            $output = $this->post('/containers/' . $id . '/json');
        }
        return $output;
    }


    /**
     * List processes running inside a container
     * List processes running inside the container id. On Unix systems this is done by running the ps command.
     * This endpoint is not supported on Windows.
     * mostly running
     * ps aux
     * @param string $id
     * @return mixed|string
     */
    public function listRunningProcesses(string $id) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)){
            $output = $this->get('/containers/' . $id . '/top?ps_args=aux');
        }
        return $output;
    }

    /**
     * Get container logs
     * Get stdout and stderr logs from the container id
     *  Note:   This endpoint works only for containers with
     *          the json-file or journald logging drivers.
     * @param string $id
     * @param string $date any English textual datetime description
     * @return mixed|string
     */
    public function getContainerLogs(string $id, string $date) {
        $output = "";
        if(empty($date)){
            $date = "now";
        }
        if (!empty($id) and $this->validate_id($id)){
            $output = $this->get('/containers/' . $id . '/logs?stderr=1&stdout=1&timestamps=1&follow=1&tail=10&since='.strtotime($date));
        }
        return $output;
    }

    /**
     * Inspect changes on a container’s filesystem
     * @param string $id
     * @return mixed|string
     */
    public function inspectChanges(string $id) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)){
            $output = $this->get('/containers/' . $id . '/changes');
        }
        return $output;
    }

    /**
     * Export a container
     * @param string $id
     * @return mixed|string
     */
    public function exportContainer(string $id) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)){
            $output = $this->get('/containers/' . $id . '/export');
        }
        return $output;
    }

    /**
     * Get container stats based on resource usage
     * @param string $id
     * @return mixed|string
     */
    public function statsContainer(string $id) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)){
            $output = $this->get('/containers/' . $id . '/stats');
        }
        return $output;
    }

    /**
     * Resize a container TTY
     * @param string $id
     * @param int $height
     * @param int $width
     * @return mixed|string
     */
    public function resizeTTY(string $id, int $height=40, int $width=80) {
        $output = "";

        if (!empty($id) and $this->validate_id($id)){
            $output = $this->post('/containers/' . $id . '/resize?h='.$height.'&w='.$width.'');
        }
        //You must restart the container for the resize to take effect.
        $this->restart($id);
        return $output;
    }


    /**
     * start a docker container
     * @param $id
     * @return mixed
     */
    public function start(string $id) {
        $output = "";
        if (!empty($id) and $this->validate_id($id) ) {
            $output = $this->post('/containers/' . $id . '/start', $getStatus=1);
            /**
             * Status codes:
             *        204 – no error
             *        304 – container already started
             *        404 – no such container
             *        500 – server error
             */
            if( $output !== "204"){
                return $output;
            }
        }

    }

    /**
     * stop a running docker container
     * @param string $id
     * @param int $time
     * @return mixed
     */
    public function stop(string $id, int $time = 5) {
        $output = "";
        if (!empty($id) and $this->validate_id($id) and $time > 2 ) {
            $output = $this->post('/containers/' . $id . '/stop?=t' . $time);
        }
	return $output;
	}

    /**
     * restart a running docker container
     * @param $id
     * @param int $time
     * @return mixed
     */
    public function restart(string $id, int $time = 5) {
        $output = "";
        if (!empty($id) and $this->validate_id($id) and $time > 2) {
            $output = $this->post('/containers/' . $id . '/restart?=t' . $time);
        }
	return $output;
	}

    /**
     * rename a docker container
     * @param string $id
     * @param string $name
     * @return mixed
     */
    public function rename(string $id, string $name) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)) {
            if(strlen($name)> 2){
                $output = $this->post('/containers/' . $id . '/rename?='. $name);
            }

        }
		
	return $output;
	}



    /**
     * kill a running docker container
     * @param $id
     * @return mixed
     */
    public function kill(string $id) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)) {
            $output = $this->post('/containers/' . $id . '/kill');
        }
	return $output;
	}

    /**
     * pause a running docker container
     * @param $id
     * @return mixed
     */
    public  function pause(string $id) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)) {
            $output = $this->post('/containers/' . $id . '/pause');
        }
	return $output;
	}

    /**
     * unpause a running docker container
     * @param $id
     * @return mixed
     */
    public  function unpause(string $id) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)) {
            $output = $this->post('/containers/' . $id . '/unpause');
        }
	return $output;
	}

    /**
     * wait a running docker container
     * @param $id
     * @return mixed
     */
	public  function wait(string $id) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)) {
            $output = $this->post('/containers/' . $id . '/wait');
        }
	return $output;
	}

    /**
     * remove a running docker container
     * @param $id
     * @return mixed
     */
	public  function remove(string $id) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)) {
            $output = $this->del('/containers/' . $id . '?v=1&force=1');
        }
	return $output;
	}

    /**
     * updateing a running docker container.
     * YOLO
     * @param string $id
     * @param object $parameter
     * @return mixed|string
     */
    public  function update(string $id, object $parameter) {
        $output = "";
        if(!$this->badJSON($parameter)){
            die("JSON file is NULL or have a typo. Please check.");
        }
        if (!empty($id) and $this->validate_id($id)) {
             $output = $this->post('/containers/' . $id . '/update', $parameter);
        }
        return $output;
    }

    /**
     * Attach to a container
     * @param string $id
     * @param bool $websocked
     * @param int $log
     * @param int $stream
     * @param int $stdin
     * @param int $stdout
     * @param int $stderr
     * @return mixed|string
     */
    public  function attach(string $id, bool $websocked=0, $log=1, $stream=0, $stdin=1, $stdout=0, $stderr=1) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)) {
            $args = '?logs='.$log.'&stream='.$stream.'&stdin='.$stdin.'&stdout='.$stdout.'&stderr='.$stderr;
            if($websocked == 1){
                $output = $this->get('/containers/' . $id . '/attach/ws'.$args);
            }else{
                $output = $this->post('/containers/' . $id . '/attach'.$args);
            }


        }
        return $output;
    }

    /**
     * Get an tar archive of a filesystem resource in a container
     * Note:    It is not possible to copy certain system files
     *          such as resources under /proc, /sys, /dev, and
     *          mounts created by the user in the container.
     * @param string $id
     * @return mixed|string
     */
    public  function getArchive(string $id) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)) {
            $output = $this->get('/containers/' . $id . '/archive?path=/root');
        }
        return $output;
    }

    /**
     * pack a Archive and move it to /root
     * same as getArchive()
     * @param string $id
     */
    public  function packArchive(string $id) {
        $this->getArchive($id);
    }


    /**
     * Extract an archive of files or folders to a directory in a container.
     * Upload a tar archive to be extracted to a path in the filesystem of container id.
     * @param string $id
     * @param object $json
     * @return mixed|string
     */
    public  function unpackArchive(string $id, object $json) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)) {
            $output = $this->put('/containers/' . $id . '/archive', $json);
        }
        return $output;
    }



    /**
     * Inspect an image
     * @param string $id
     * @return mixed|string
     */
    public  function inspectImage(string $id) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)) {
            $output = $this->get('/images/' . $id . '/json');
        }
        return $output;
    }

    /**
     * Get the history of an image
     * @param string $id
     * @return mixed|string
     */
    public  function gethistoryImage(string $id) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)) {
            $output = $this->get('/images/' . $id . '/history');
        }
        return $output;
    }

    /**
     * Push an image on the registry
     * @param string $id
     * @return mixed|string
     */
    public  function pushImageToReg(string $id) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)) {
            $output = $this->post('/images/' . $id . '/push');
        }
        return $output;
    }


    /**
     * Tag an image into a repository
     * @param string $id
     * @return mixed|string
     */
    public  function tagImageinRepo(string $id, string $repo, string $tag) {
        $output = "";
        if (!empty($id) and $this->validate_id($id)) {
            $output = $this->post('/images/' . $id . '/tag?repo='.$repo.'&tag='.$tag);
        }
        return $output;
    }


    /**
     * Search for an image on Docker Hub.
     * @param string $needle
     * @return mixed
     */
    public  function searchOnDockerHUB(string $needle) {
        $output = $this->get('/images/search/?term='.$needle);
        return $output;
    }





    /**
     * List Images
     * @return mixed
     */
    public function listdocks() {
		$output = $this->get('/images/json?all=0&size=1');
 	
	return $output;
	}


    /**
     * List all running Images
     * @return mixed
     */
    public function listdocksrunning() {
		$output = $this->get('/containers/json?all=1&status=running&size=1');
		
	return $output;
	}

    /**
     * Display system-wide information
     * @return mixed
     */
    public function info() {
		$output = $this->get('/info');
		
	return $output;
	}

    /**
     * get versions info form docker
     * @return mixed
     */
    public function version() {
		$output = $this->get('/version');
		
	return $output;
	}
	
	public function exec(string $id, array $cmd = array()) {
		$body = [
			'AttachStdin'  => FALSE,
			'AttachStdout' => TRUE,
			'AttachStderr' => TRUE,
			'Tty'          => FALSE,
			'Cmd' => $cmd
		];
		$bodyJSON = json_encode($body);
		
		$params = [   
			'body'         => $bodyJSON,
			'headers'      => ['content-type' => 'application/json']
		];
        $output = "";
        if (!empty($id) and $this->validate_id($id)) {
            $output = $this->post('/containers/' . $id . '/exec', $params);
        }
	return $output;
	}
	
}