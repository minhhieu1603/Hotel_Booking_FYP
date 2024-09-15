function get_bookings()
{
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/new_bookings.php",true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function(){
        document.getElementById('table-data').innerHTML = this.responseText;
    }

    xhr.send('get_bookings');
}

let assign_room_form = document.getElementById('assign_room_form');

function assign_room(id){
    assign_room_form.elements['booking_id'].value=id;
}

assign_room_form.addEventListener('submit',function(e){
    e.preventDefault();

    let data = new FormData();
    data.append('room_no', assign_room_form.elements['room_no'].value);
    data.append('booking_id', assign_room_form.elements['booking_id'].value);
    data.append('assign_room','');
    

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/new_bookings.php",true);

    xhr.onload = function(){
        var myModal = document.getElementById('assign-room');
        var modal = bootstrap.Modal.getInstance(myModal);
        modal.hide();

        if(this.responseText==1){
            alert('success','Room Number Allotted! Booking Finalized!');
            assign_room_form.reset();
            get_bookings();
        }
        else{
            alert('error','Server Down!');
        }     
    }

    xhr.send(data);
});

// function remove_user(user_id)
// {
//     if(confirm("Are you sure, you want to remove this user?"))
//     {
//         let data = new FormData();
//         data.append('user_id', user_id);
//         data.append('remove_user', '');

//         let xhr = new XMLHttpRequest();
//         xhr.open("POST", "ajax/users.php",true);

//         xhr.onload = function()
//         {
//             if(this.responseText == 1){
//                 alert('success', 'User Removed!');
//                 get_users();  
//             }
//             else{
//                 alert('error', 'User removal failed!');  
//             }
//         }
//         xhr.send(data);       
//     }
// }

// function search_user(username){
//     let xhr = new XMLHttpRequest();
//     xhr.open("POST", "ajax/users.php",true);
//     xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

//     xhr.onload = function(){
//         document.getElementById('users-data').innerHTML = this.responseText;
//     }

//     xhr.send('search_user&name='+username);
// }

window.onload = function(){
    get_bookings();
}