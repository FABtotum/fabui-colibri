# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.
  config.vm.box = "fabtotum/centosdev"

  # Disable vagrant default share
  config.vm.synced_folder ".", "/vagrant", disabled: true

  # Try sharing SD card output and FABui repository for development
  config.vm.synced_folder ".", "/mnt/development/colibri-fabui"

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  config.vm.provider "virtualbox" do |vb|
    # Display the VirtualBox GUI when booting the machine
    vb.gui = true

    # Customize the amount of memory on the VM:
    vb.memory = "2048"
  end

  # Install devleopment dependencies and download code repositories
  config.vm.provision "shell", path: "tools/install_deps.sh"
  config.vm.provision "shell", path: "tools/update_repos.sh"

  # Set development user name
  config.ssh.username = "developer"
end
