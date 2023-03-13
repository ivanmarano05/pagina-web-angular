import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from 'src/environments/environment';


@Component({
  selector: 'app-mi-equipo',
  templateUrl: './mi-equipo.component.html',
})
export class MiEquipoComponent implements OnInit {

  miEquipo: any = {};
  public jugadores: any = [];

  constructor(private http: HttpClient) { }

  getEquipo (): void {
    this.http.get(environment.baseUrl + "equipo")
       .subscribe({
          next: (response) => {
             this.miEquipo = response;
             this.getJugadores(this.miEquipo.id);
          },
          error: (e) => {}
       });       
 }

 getJugadores (id: number): void {
  this.http.get(environment.baseUrl + "jugadores/" + id)
     .subscribe({
        next: (response) => {
           this.jugadores = response;
        },
        error: (e) => {}
     });
}

 ngOnInit() {
    /*this.http.get(environment.baseUrl + "miEquipo", {})
      .subscribe({
        next: (response) => {
          this.miEquipo = response;
        },
        error: (err) => {
          console.error(err)
        },
        complete: () => {}
      });*/
      this.getEquipo();
  }

}
