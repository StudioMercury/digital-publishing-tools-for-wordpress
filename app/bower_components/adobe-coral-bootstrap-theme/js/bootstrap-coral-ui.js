/*
//
// Coral UI Specific Functions
// --------------------------------------------------
*/

$( document ).ready(function() {
    
    $( ".coral-sidebar" ).on( "click", toggleSidebar);
    
});

function toggleSidebar() {
    $( ".coral-sidebar" ).toggleClass('open');
    $( ".page-view" ).toggleClass('coral-sidebar-open');   
}