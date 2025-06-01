FROM jenkins/jenkins:lts-jdk17

USER root
RUN apt-get update && \
    apt-get install -y docker.io && \
    groupadd -g 999 docker || true && \
    usermod -aG docker jenkins

USER jenkins
