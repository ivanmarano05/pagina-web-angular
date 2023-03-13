import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { VerEquiposComponent } from '../ver-equipos/ver-equipos.component';
import { environment } from 'src/environments/environment';
import { ActivatedRoute } from '@angular/router';


@Component({
  selector: 'app-detalles-equipo',
  templateUrl: './detalles-equipo.component.html',
})
export class DetallesEquipoComponent implements OnInit {

  public equipo: any;
  public jugadores: any = [];
  public idEquipo: any;

  constructor(private http: HttpClient, private _route: ActivatedRoute) { }

  getEquipo (): void {
    this.http.get(environment.baseUrl + "equipo/" + this.idEquipo)
       .subscribe({
          next: (response) => {
             this.equipo = response;
             this.getJugadores(this.equipo.id);
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
      this.idEquipo = this._route.snapshot.paramMap.get('id');

      this.getEquipo();
  }

}
