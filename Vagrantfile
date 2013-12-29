Vagrant.configure("2") do |config|

  config.vm.define :saucy64 do |saucy64|
    saucy64.omnibus.chef_version = :latest
    saucy64.vbguest.auto_update = true
    saucy64.ssh.forward_agent = true
    saucy64.cache.auto_detect = true
    saucy64.cache.enable_nfs  = true
    saucy64.vm.box = "skylab-saucy64"
    saucy64.vm.box_url = "http://opscode-vm-bento.s3.amazonaws.com/vagrant/virtualbox/opscode_ubuntu-13.10_chef-provisionerless.box"
    saucy64.vm.network :private_network, ip: "33.33.33.33"
    saucy64.vm.network :forwarded_port, guest: 80,    host: 8080
    saucy64.vm.hostname = 'saucy64.kunstmaan.be'
    saucy64.vm.provider :virtualbox do |vb|
      vb.customize ["modifyvm", :id, "--memory", "1024"]
    end
    saucy64.vm.provision :chef_solo do |chef|
     chef.cookbooks_path = "cookbooks"
     chef.add_recipe "skylab::default"
     chef.log_level = :debug
    end
  end

end
