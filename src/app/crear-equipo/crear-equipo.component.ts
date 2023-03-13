import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { environment } from 'src/environments/environment';


@Component({
  selector: 'app-crear-equipo',
  templateUrl: './crear-equipo.component.html',
})
export class CrearEquipoComponent implements OnInit {

  jugadores: any = [];
  pelotas: any = [];
  unEquipo: any = {};

  constructor(private router: Router, private http: HttpClient) { }

  getEquipo (): void {
    this.http.get(environment.baseUrl + "equipo")
       .subscribe({
          next: (response) => {
             this.unEquipo = response;
          },
          error: (e) => {}
       });       
 }

 guardar(): void {
  if (this.unEquipo.id != null) {
     this.http.patch(environment.baseUrl + "equipo/" + this.unEquipo.id, this.unEquipo)
        .subscribe({
           next: (response) => {
              this.unEquipo = {};
              this.router.navigate(['/miEquipo']);
           },
           error: (e) => {
            alert(e.error);
           }
        });
  } else {
     this.http.post(environment.baseUrl + "equipo", this.unEquipo)
        .subscribe({
           next: (response) => {
              this.unEquipo = {};
              this.router.navigate(['/miEquipo']);
           },
           error: (e) => {
            alert(e.error);
           }
        });
  }
}

descartar(): void {
   
}

 /*getJugadores (id: number): void {
  this.http.get(environment.baseUrl + "jugadores/" + id)
     .subscribe({
        next: (response) => {
           this.jugadores = response;
        },
        error: (e) => {}
     });
}*/

  listarPelotas (): void {
    this.http.get(environment.baseUrl + "pelotas")
        .subscribe({
            next: (response) => {
              this.pelotas = response;
            },
            error: (e) => {}
        });
  }

  listarJugadores (): void {
    this.http.get(environment.baseUrl + "jugadores")
      .subscribe({
          next: (response) => {
            this.jugadores = response;
          },
          error: (e) => {}
      });
}

  ngOnInit() {
      this.getEquipo();
      this.listarPelotas();
      this.listarJugadores();
  }

}
