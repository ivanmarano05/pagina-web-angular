import { Component, OnInit } from '@angular/core';
import { FormBuilder } from '@angular/forms';
import { FormGroup } from '@angular/forms';
import { ChangeDetectorRef } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from 'src/environments/environment';
import { DomSanitizer } from '@angular/platform-browser';

@Component({
  selector: 'app-foro',
  templateUrl: './foro.component.html',
})
export class ForoComponent implements OnInit {

  public mensajes: any = [];
  public unMensaje: any = {};

  uploadImageForm!: FormGroup;
  profileImage: any;
  Imageloaded: boolean = false;
  thumbnail: any;

  constructor(private http: HttpClient, private fb:FormBuilder, private changeDetector:ChangeDetectorRef, private domSanitizer: DomSanitizer) {
  }

  getMensajes (): void {
    this.http.get(environment.baseUrl + "mensajes")
       .subscribe({
          next: (response) => {
             this.mensajes = response;
             //this.sanitizarImagenes();
          },
          error: (e) => {}
       });       
  }

  sanitizarImagenes (): void {
   for(let i = 0; i < this.mensajes.length; i++) {
      let objectURL = 'data:image/jpeg;base64,' + this.mensajes[i].imagen;
      console.log("Tamaño de arreglo Mensajes:" + this.mensajes.length);
      this.thumbnail = this.domSanitizer.bypassSecurityTrustUrl(objectURL);
   }
  }

 guardar(): void {
  this.http.post(environment.baseUrl + "mensaje", this.unMensaje)
       .subscribe({
          next: (response) => {
              this.unMensaje = {};
           },
           error: (e) => {
            alert(e.error);
           }
        });
        window.location.reload();
  }

 imageUpload(event:any) {
    var file = event.target.files.length;
    for(let i = 0; i < file; i++) {
       var reader = new FileReader();
       reader.onload = (event:any) => 
       {
           this.profileImage = event.target.result;
           this.changeDetector.detectChanges();
       }
       reader.readAsDataURL(event.target.files[i]);
    }
  }

  handleImageLoad() {
    this.Imageloaded = true;
  }

  onSubmit() {
     var Image = this.profileImage; //get Image Base64
     this.unMensaje.imagen = this.profileImage;
     console.log("Imagen Image:" + Image);
     console.log("Imagen mensajes:" + this.mensajes[0].imagen);
  }

  ngOnInit() {
      this.getMensajes();
      this.uploadImageForm = this.fb.group({
         Image:['']
       });
  }

}