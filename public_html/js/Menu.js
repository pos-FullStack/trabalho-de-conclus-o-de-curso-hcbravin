/*!
* Start Bootstrap - Simple Sidebar v6.0.6 (https://startbootstrap.com/template/simple-sidebar)
* Copyright 2013-2023 Start Bootstrap
* Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-simple-sidebar/blob/master/LICENSE)
*/
// 
// Scripts
// 

window.addEventListener('DOMContentLoaded', event => {

  // Toggle the side navigation
  const sidebarToggle = document.body.querySelector('#sidebarToggle');
  if (sidebarToggle) {
      // Uncomment Below to persist sidebar toggle between refreshes
      // if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
      //     document.body.classList.toggle('sb-sidenav-toggled');
      // }
      sidebarToggle.addEventListener('click', event => {
          event.preventDefault();
          document.body.classList.toggle('sb-sidenav-toggled');
          localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
      });
  }

});


$(function(){

  function LeftBar(){
    let cookies = document.cookie.split(';')
    for(let i=0;i<=(cookies.length - 1);i++){
      let cookie = cookies[i].split("=");
      if(cookie[0].trim()=='leftBar' && cookie[1]=='true'){
//        document.body.classList.toggle('sb-sidenav-toggled');  
      break; }
    }
  } LeftBar();

  $('#sidebarToggle').click(function(){
    let bar = $('#sidebar-wrapper').css('margin-left'); // 0px is hide
    document.cookie = 'leftBar='+(bar=='0px'?'true':'false')+'; expires=1 Jan 2123 00:00:00 UTC; path=/';
    LeftBar();
  });

});