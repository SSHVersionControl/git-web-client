FROM ubuntu:16.04

RUN apt-get update && apt-get install -y openssh-server git
RUN mkdir /var/run/sshd
RUN echo 'root:root' | chpasswd
RUN sed -i 's/PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config

# SSH login fix. Otherwise user is kicked off after login
RUN sed 's@session\s*required\s*pam_loginuid.so@session optional pam_loginuid.so@g' -i /etc/pam.d/sshd

ENV NOTVISIBLE "in users profile"
RUN echo "export VISIBLE=now" >> /etc/profile

RUN mkdir /var/www
RUN cd /var/www/ && git clone https://github.com/SSHVersionControl/git-web-client.git

EXPOSE 22
CMD ["/usr/sbin/sshd", "-D"]
