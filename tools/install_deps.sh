#!/usr/bin/env bash

echo "Updating system..."
sudo yum makecache fast
sudo yum update -y
sudo yum install -y epel-release
sudo yum install y dkms

echo "Installing/updating development environment..."
sudo yum install -y gcc-c++ libstdc++-static glibc-static
sudo yum install -y git flex bison patch tree autoconf automake ncurses-devel fakeroot

echo "Installing development dependencies..."
sudo yum install -y perl-ExtUtils-MakeMaker

echo "Installing/updating FABEmu dependencies"
sudo yum install -y qemu-system-arm squashfs-tools gst123
