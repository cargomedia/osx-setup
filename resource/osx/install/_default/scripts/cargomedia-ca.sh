curl -sL 'http://www.cargomedia.ch/files/cargomedia-ca.pem' > cargomedia-ca.pem
sudo security add-trusted-cert -d -k /Library/Keychains/System.keychain cargomedia-ca.pem
