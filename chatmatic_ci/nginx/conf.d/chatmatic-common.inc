    keepalive_timeout   120;
    client_max_body_size 60m;

    #set_real_ip_from 10.15.1.81;
    #set_real_ip_from 10.15.1.72;
    #real_ip_header X-Forwared-For;
    #real_ip_recursive on;
    # TODO: turn gzip on for just a portion of the site via location /{} section
    location /pipeline {
            #uwsgi_pass unix:///run/uwsgi/chatmatic.sock;
            # not using unix socket because we want GoReplay be able
            # to sniff for HTTP requests and replay then to testing env
            include conf.d/proxy_params;
            set        $dest "pipeline";
            proxy_pass http://$dest:9000;
    }
    location /adminer {
            auth_basic "Restricted area";
            auth_basic_user_file /etc/nginx/conf.d/htpasswd;
            include conf.d/proxy_params;
            set        $dest "adminer";
            proxy_pass http://$dest:8080;
    }
    location /.well-known {
            include conf.d/proxy_params;
            set         $dest "letsencrypt";
            proxy_pass  http://$dest:80;
    }
    location / {
	    return 301 https://app.chatmatic.com;
    }

