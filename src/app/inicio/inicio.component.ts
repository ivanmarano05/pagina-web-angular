import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from 'src/environments/environment';


@Component({
  selector: 'app-inicio',
  templateUrl: './inicio.component.html',
})
export class InicioComponent implements OnInit {

  constructor(private http: HttpClient) { }

  ngOnInit() { }
}
