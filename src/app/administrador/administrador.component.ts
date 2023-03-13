import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from 'src/environments/environment';
import { DomSanitizer } from '@angular/platform-browser';


@Component({
  selector: 'app-administrador',
  templateUrl: './administrador.component.html',
})
export class AdministradorComponent implements OnInit {

  public usuarios: any = [];
  public mensajes: any = [];

  thumbnail: any;

  constructor(private http: HttpClient, private domSanitizer: DomSanitizer) { }

  getUsuarios (): void {
    this.http.get(environment.baseUrl + "usuarios")
       .subscribe({
          next: (response) => {
             this.usuarios = response;
          },
          error: (e) => {}
       });       
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

 borrarUsuario(id: number): void {
  if (confirm('¿Está seguro que desea borrar el usuario?')) {
     this.http.delete(environment.baseUrl + "usuario/" + id)
        .subscribe({
           next: (response) => {
              this.getUsuarios();
           },
           error: (e) => {}
        });
  }
 }

 borrarMensaje(id: number): void {
   if (confirm('¿Está seguro que desea borrar la publicación?')) {
      this.http.delete(environment.baseUrl + "mensaje/" + id)
         .subscribe({
            next: (response) => {
               this.getMensajes();
            },
            error: (e) => {}
         });
   }
  }

  ngOnInit() {
      this.getUsuarios();
      this.getMensajes();
  }

}
