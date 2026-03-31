function validateForm(){
let n=document.getElementById("name").value;
let e=document.getElementById("email").value;
let np=/^[A-Za-z ]{3,}$/;
let ep=/^[^\s@]+@[^\s@]+\.[^\s@]+$/;
if(!np.test(n) || !ep.test(e)){
alert("Invalid Name or Email");
return false;
}
return true;
}