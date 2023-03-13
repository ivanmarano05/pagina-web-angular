import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from 'src/environments/environment';


@Component({
  selector: 'app-ver-equipos',
  templateUrl: './ver-equipos.component.html',
})
export class VerEquiposComponent implements OnInit {

  public verEquipos: any;
  public equipos: any = [];

  constructor(private http: HttpClient) { }

  getEquipos (): void {
    this.http.get(environment.baseUrl + "equipos")
       .subscribe({
          next: (response) => {
             this.equipos = response;
             //this.getJugadores(this.miEquipo.id);
          },
          error: (e) => {}
       });       
 }

  ngOnInit() {
    /*this.http.get(environment.baseUrl + "verEquipos", {})
      .subscribe({
        next: (response) => {
          this.verEquipos = response;
        },
        error: (err) => {
          console.error(err)
        },
        complete: () => {}
      });*/
      this.getEquipos();
  }

}
