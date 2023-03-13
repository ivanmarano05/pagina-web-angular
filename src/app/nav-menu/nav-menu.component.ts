import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { JwtHelperService } from '@auth0/angular-jwt';
import { AutenticacionService } from 'src/app/autenticacion.service';
import { HttpClient } from '@angular/common/http';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-nav-menu',
  templateUrl: './nav-menu.component.html',
})
export class NavMenuComponent implements OnInit {

  public jwtHelper: JwtHelperService = new JwtHelperService();

  public isExpanded = false;
  public usuario: any;
  public rol: any = {};
  public esAdmin: boolean = false;

  constructor(private router: Router, public autenticador: AutenticacionService, private http: HttpClient) { }  

  getUsuario (): void {
    this.http.get(environment.baseUrl + "usuario")
       .subscribe({
          next: (response) => {
            this.rol = response;
            if(this.rol.rol == 'admin')
              this.esAdmin = true;
              //window.location.reload();
            },
          error: (e) => {}
       });
 }

 /*esAdminn(): void {
   if(this.rol.rol == 'admin'){
      this.esAdmin = true; 
   }
 }*/

  public logOut = () => {
    if (this.autenticador.logout()) {
      this.router.navigate(['/']);
      this.usuario = null;
      this.rol = {};
      this.esAdmin = false;
    } else {
      alert('Error en logout');
    }
  }

  ngOnInit() {
      this.getUsuario();
  }
}